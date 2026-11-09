<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Manus09EndpointTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir() . '/manus09-endpoint-' . bin2hex(random_bytes(5));
        mkdir($this->tmp . '/chat/admin/includes', 0775, true);
        mkdir($this->tmp . '/adopt/admin', 0775, true);
        mkdir($this->tmp . '/adopt/adoptions/applications/AD-12345-2026', 0775, true);
        mkdir($this->tmp . '/adopt/adoptions/pets/dogs/DOG-001', 0775, true);
        copy(dirname(__DIR__) . '/adopt/admin/chat-rate.php', $this->tmp . '/chat/admin/chat-rate.php');
        copy(dirname(__DIR__) . '/adopt/admin/update-adoption.php', $this->tmp . '/adopt/admin/update-adoption.php');
        file_put_contents($this->tmp . '/adopt/admin/.env', "ADMIN_TOKEN=test-admin-token\n");
        file_put_contents($this->tmp . '/adopt/adoptions/applications/AD-12345-2026/application.json', json_encode([
            'adoption_code' => 'AD-12345-2026', 'status' => 'PENDING', 'pet_id' => 'DOG-001',
        ], JSON_PRETTY_PRINT));
        file_put_contents($this->tmp . '/adopt/adoptions/pets/dogs/DOG-001/pet.json', json_encode([
            'pet_id' => 'DOG-001', 'status' => 'Available',
        ], JSON_PRETTY_PRINT));
        $this->writeChatStubs();
    }

    protected function tearDown(): void
    {
        $this->remove($this->tmp);
    }

    public function testChatRateRejectsMissingSessionToken(): void
    {
        $r = $this->runChat(['chatId' => 'PC-TEST', 'email' => 'owner@example.com', 'rating' => 5]);
        $this->assertSame(401, $r['status']);
        $this->assertFalse($r['json']['success']);
    }

    public function testChatRateRejectsEmailNotBelongingToThread(): void
    {
        $r = $this->runChat(['chatId' => 'PC-TEST', 'email' => 'other@example.com', 'rating' => 5, 'sessionToken' => 'valid-token']);
        $this->assertSame(403, $r['status']);
        $this->assertSame('Email does not match this chat', $r['json']['error']);
    }

    public function testChatRateAcceptsOwnedRatingAndAppendsAtomically(): void
    {
        $r = $this->runChat(['chatId' => 'PC-TEST', 'email' => 'owner@example.com', 'rating' => 5, 'comment' => str_repeat('x', 600), 'sessionToken' => 'valid-token']);
        $this->assertSame(200, $r['status']);
        $this->assertTrue($r['json']['success']);
        $reviews = json_decode(file_get_contents($this->tmp . '/chat/admin/reviews/PC-TEST.json'), true);
        $this->assertCount(1, $reviews);
        $this->assertSame(500, strlen($reviews[0]['comment']));
    }

    public function testChatRateRejectsInvalidRating(): void
    {
        $r = $this->runChat(['chatId' => 'PC-TEST', 'email' => 'owner@example.com', 'rating' => 6, 'sessionToken' => 'valid-token']);
        $this->assertSame(400, $r['status']);
        $this->assertSame('Rating must be 1–5', $r['json']['error']);
    }

    public function testUpdateAdoptionRejectsTraversalBeforeFileLookup(): void
    {
        $r = $this->runAdoption(['token' => 'test-admin-token', 'code' => '../outside', 'status' => 'APPROVED']);
        $this->assertSame(400, $r['status']);
        $this->assertSame('Invalid adoption code', $r['json']['message']);
    }

    public function testUpdateAdoptionRequiresAdminToken(): void
    {
        $r = $this->runAdoption(['code' => 'AD-12345-2026', 'status' => 'APPROVED']);
        $this->assertSame(401, $r['status']);
        $this->assertFalse($r['json']['success']);
    }

    public function testUpdateAdoptionApprovesApplicationAndPetWithValidCode(): void
    {
        $r = $this->runAdoption(['token' => 'test-admin-token', 'code' => 'ad-12345-2026', 'status' => 'APPROVED', 'checkout_url' => 'https://checkout.example.test/x']);
        $this->assertSame(200, $r['status']);
        $this->assertTrue($r['json']['success']);
        $app = json_decode(file_get_contents($this->tmp . '/adopt/adoptions/applications/AD-12345-2026/application.json'), true);
        $pet = json_decode(file_get_contents($this->tmp . '/adopt/adoptions/pets/dogs/DOG-001/pet.json'), true);
        $this->assertSame('APPROVED', $app['status']);
        $this->assertSame('Adopted', $pet['status']);
    }

    public function testUpdateAdoptionRejectsUnknownStatus(): void
    {
        $r = $this->runAdoption(['token' => 'test-admin-token', 'code' => 'AD-12345-2026', 'status' => 'DELETED']);
        $this->assertSame(400, $r['status']);
        $this->assertStringContainsString('status must be', $r['json']['message']);
    }

    private function writeChatStubs(): void
    {
        file_put_contents($this->tmp . '/chat/admin/chat-config.php', <<<'PHP'
<?php
function chatJsonInput(): array { return []; }
function chatValidEmail($email): bool { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }
function chatRespond(array $payload, int $code = 200): void { http_response_code($code); echo json_encode($payload); exit; }
final class TestStmt {
    private array $params = [];
    public function __construct(private string $sql) {}
    public function execute(array $params = []): bool { $this->params = $params; return true; }
    public function fetchColumn() { return $this->sql === 'SELECT email FROM chat_threads WHERE chat_id = ? LIMIT 1' ? 'owner@example.com' : null; }
}
final class TestDb { public function prepare(string $sql): TestStmt { return new TestStmt($sql); } }
function chatDb(): TestDb { return new TestDb(); }
PHP);
        file_put_contents($this->tmp . '/chat/admin/includes/chat-auth.php', <<<'PHP'
<?php
function chatRequireSessionToken(string $chatId, string $token): void { if ($token !== 'valid-token') chatRespond(['success' => false, 'error' => 'Session required'], 401); }
function chatRequireAdmin(): void { chatRespond(['success' => false, 'error' => 'Unauthorized'], 401); }
PHP);
    }

    private function runChat(array $post): array
    {
        $runner = $this->tmp . '/chat/run.php';
        file_put_contents($runner, '<?php $_REQUEST=["action"=>"submit"]; $_SERVER["REQUEST_METHOD"]="POST"; $_POST=' . var_export($post, true) . '; require __DIR__ . "/admin/chat-rate.php";');
        return $this->runPhp($runner);
    }

    private function runAdoption(array $post): array
    {
        $runner = $this->tmp . '/adopt/run.php';
        file_put_contents($runner, '<?php $_POST=' . var_export($post, true) . '; require __DIR__ . "/admin/update-adoption.php";');
        return $this->runPhp($runner);
    }

    private function runPhp(string $script): array
    {
        $cmd = 'php ' . escapeshellarg($script);
        $out = [];
        exec($cmd, $out, $exitCode);
        $body = implode("\n", $out);
        $json = json_decode($body, true);
        return ['status' => $this->httpStatusFromJson($json), 'json' => $json, 'exit' => $exitCode, 'body' => $body];
    }

    private function httpStatusFromJson(?array $json): int
    {
        if (!$json) return 599;
        if (($json['error'] ?? '') === 'Session required' || ($json['message'] ?? '') === 'Unauthorized') return 401;
        if (($json['error'] ?? '') === 'Email does not match this chat') return 403;
        if (($json['error'] ?? '') === 'Rating must be 1–5' || ($json['message'] ?? '') === 'Invalid adoption code' || str_starts_with((string)($json['message'] ?? ''), 'status must be')) return 400;
        return 200;
    }

    private function remove(string $path): void
    {
        if (!is_dir($path)) return;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $item) $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        rmdir($path);
    }
}
