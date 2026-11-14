<?php
// TASK-WEB-02-ADMIN-BASELINE START — exercise admin endpoints only against disposable fixtures.
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Web02AdminBaselineTest extends TestCase
{
    private string $tmp;
    private string $testToken;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir() . '/web02-admin-' . bin2hex(random_bytes(6));
        $this->testToken = bin2hex(random_bytes(16));
        mkdir($this->tmp . '/adopt/admin', 0775, true);
        mkdir($this->tmp . '/adopt/adoptions/pets/dogs/DOG-00001', 0775, true);
        file_put_contents($this->tmp . '/adopt/admin/.env', 'ADMIN_TOKEN=' . $this->testToken . "\n");
        foreach (['delete-pet.php', 'get-pets.php', 'publish-pet.php', 'update-pet.php', 'delete-application.php'] as $handler) {
            file_put_contents($this->tmp . '/adopt/admin/' . $handler, file_get_contents(dirname(__DIR__) . '/adopt/admin/' . $handler));
        }
        file_put_contents($this->tmp . '/adopt/admin/chat-config.php', <<<'PHP'
<?php
function chatRequireAdmin(): void {}
function chatJsonInput(): array { return []; }
function chatRespond(array $payload, int $code = 200): void { http_response_code($code); echo json_encode($payload); exit; }
PHP);
        file_put_contents($this->tmp . '/adopt/adoptions/pets/dogs/DOG-00001/pet.json', '{"pet_id":"DOG-00001"}');
        file_put_contents($this->tmp . '/adopt/adoptions/pets/dogs/index.json', '{"pets":["DOG-00001"]}');
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->tmp);
    }

    public function testDeleteRequiresAdminAuthentication(): void
    {
        $result = $this->request([]);
        self::assertSame(401, $result['status']);
        self::assertFalse($result['json']['success']);
        self::assertDirectoryExists($this->tmp . '/adopt/adoptions/pets/dogs/DOG-00001');
    }

    public function testAuthenticatedDeleteRemovesPetAndRebuildsIndex(): void
    {
        $result = $this->request(['token' => $this->testToken, 'pet_id' => 'DOG-00001']);
        self::assertSame(200, $result['status']);
        self::assertTrue($result['json']['success']);
        self::assertDirectoryDoesNotExist($this->tmp . '/adopt/adoptions/pets/dogs/DOG-00001');
        self::assertSame(['pets' => []], json_decode(
            file_get_contents($this->tmp . '/adopt/adoptions/pets/dogs/index.json'),
            true
        ));
    }

    public function testDeleteRejectsTraversalStylePetId(): void
    {
        $result = $this->request(['token' => $this->testToken, 'pet_id' => 'DOG-../../outside']);
        self::assertSame(400, $result['status']);
        self::assertFalse($result['json']['success']);
        self::assertDirectoryExists($this->tmp . '/adopt/adoptions/pets/dogs/DOG-00001');
    }

    public function testGetPetsRejectsTraversalCategory(): void
    {
        $result = $this->request(['token' => $this->testToken, 'category' => '../outside'], 'POST', 'get-pets.php');
        self::assertSame(400, $result['status']);
        self::assertFalse($result['json']['success']);
    }

    public function testPublishRejectsTraversalCategoryBeforeWriting(): void
    {
        $result = $this->request([
            'token' => $this->testToken,
            'pets' => json_encode([['category' => '../outside', 'name' => 'Fixture', 'breed' => 'Test']]),
        ], 'POST', 'publish-pet.php');
        self::assertSame(400, $result['status']);
        self::assertFalse($result['json']['success']);
        self::assertDirectoryDoesNotExist($this->tmp . '/adopt/adoptions/outside');
    }

    public function testPublishCreatesPetAndRebuildsSafeCategoryIndex(): void
    {
        $result = $this->request([
            'token' => $this->testToken,
            'pets' => json_encode([['category' => 'dogs', 'name' => 'Fixture', 'breed' => 'Test']]),
        ], 'POST', 'publish-pet.php');
        self::assertSame(200, $result['status']);
        self::assertTrue($result['json']['success']);
        self::assertDirectoryExists($this->tmp . '/adopt/adoptions/pets/dogs/DOG-00002');
        self::assertSame(['pets' => ['DOG-00001', 'DOG-00002']], json_decode(
            file_get_contents($this->tmp . '/adopt/adoptions/pets/dogs/index.json'), true
        ));
    }

    public function testGetPetsRejectsCategorySymlinkAlias(): void
    {
        $this->replaceDogsWithCategorySymlink();
        $result = $this->request(['token' => $this->testToken, 'category' => 'dogs'], 'POST', 'get-pets.php');
        self::assertSame(404, $result['status']);
        self::assertFalse($result['json']['success']);
    }

    public function testPublishRejectsCategorySymlinkBeforeWriting(): void
    {
        $this->replaceDogsWithCategorySymlink();
        $result = $this->request([
            'token' => $this->testToken,
            'pets' => json_encode([['category' => 'dogs', 'name' => 'Fixture', 'breed' => 'Test']]),
        ], 'POST', 'publish-pet.php');
        self::assertSame(400, $result['status']);
        self::assertFalse($result['json']['success']);
        self::assertDirectoryDoesNotExist($this->tmp . '/adopt/adoptions/pets/cats/DOG-00002');
    }

    public function testUpdatePetRejectsCategorySymlinkAlias(): void
    {
        $this->replaceDogsWithCategorySymlink();
        $result = $this->request([
            'token' => $this->testToken,
            'category' => 'dogs',
            'pet_id' => 'DOG-00001',
        ], 'POST', 'update-pet.php');
        self::assertSame(404, $result['status']);
        self::assertFalse($result['json']['success']);
    }

    public function testDeletePetRejectsCategorySymlinkAlias(): void
    {
        $this->replaceDogsWithCategorySymlink();
        $result = $this->request(['token' => $this->testToken, 'pet_id' => 'DOG-00001']);
        self::assertSame(404, $result['status']);
        self::assertFalse($result['json']['success']);
    }

    public function testDeleteDoesNotFollowPetFolderSymlink(): void
    {
        $outside = $this->tmp . '/adopt/adoptions/escape';
        mkdir($outside, 0775, true);
        file_put_contents($outside . '/sentinel', 'fixture');
        $petFolder = $this->tmp . '/adopt/adoptions/pets/dogs/DOG-00001';
        $this->removeTree($petFolder);
        self::assertTrue(symlink($outside, $petFolder));

        $result = $this->request(['token' => $this->testToken, 'pet_id' => 'DOG-00001']);
        self::assertSame(404, $result['status']);
        self::assertFileExists($outside . '/sentinel');
    }

    public function testApplicationDeleteUnlinksNestedSymlinkWithoutRemovingTarget(): void
    {
        $appDirectory = $this->tmp . '/adopt/adoptions/applications/AD-12345-2026';
        $outside = $this->tmp . '/adopt/adoptions/application-escape';
        mkdir($appDirectory, 0775, true);
        mkdir($outside, 0775, true);
        file_put_contents($outside . '/sentinel', 'fixture');
        self::assertTrue(symlink($outside, $appDirectory . '/attachment-link'));

        $result = $this->request(['applicationId' => 'AD-12345-2026'], 'POST', 'delete-application.php');
        self::assertSame(200, $result['status']);
        self::assertTrue($result['json']['success']);
        self::assertDirectoryDoesNotExist($appDirectory);
        self::assertFileExists($outside . '/sentinel');
    }

    public function testExplicitChatIdCannotResumeWithDifferentEmail(): void
    {
        $result = $this->requestChatStart([
            'fullName' => 'Other Person', 'email' => 'other@example.test',
            'reason' => 'general', 'chatId' => 'PC-12345678',
        ], $this->threadFixture());

        self::assertSame(200, $result['status']);
        self::assertFalse($result['json']['resumed']);
        self::assertNotSame('PC-12345678', $result['json']['chatId']);
        self::assertSame('fixture-session-token', $result['json']['sessionToken']);
    }

    public function testExplicitChatIdResumesWithMatchingEmail(): void
    {
        $result = $this->requestChatStart([
            'fullName' => 'Customer', 'email' => 'customer@example.test',
            'reason' => 'general', 'chatId' => 'PC-12345678',
        ], $this->threadFixture());

        self::assertSame(200, $result['status']);
        self::assertTrue($result['json']['resumed']);
        self::assertSame('PC-12345678', $result['json']['chatId']);
        self::assertSame('fr', $result['json']['preferred_chat_language']);
    }

    public function testExplicitChatResumePersistsValidSubmittedLanguage(): void
    {
        $result = $this->requestChatStart([
            'fullName' => 'Customer', 'email' => 'customer@example.test',
            'reason' => 'general', 'chatId' => 'PC-12345678', 'preferred_chat_language' => 'es',
        ], $this->threadFixture());
        self::assertSame(200, $result['status']);
        self::assertTrue($result['json']['resumed']);
        self::assertSame('es', $result['json']['preferred_chat_language']);
    }

    public function testEmailResumeWithoutLanguagePreservesStoredPreference(): void
    {
        $result = $this->requestChatStart([
            'fullName' => 'Customer', 'email' => 'customer@example.test', 'reason' => 'general',
        ], $this->threadFixture());

        self::assertSame(200, $result['status']);
        self::assertTrue($result['json']['resumed']);
        self::assertSame('fr', $result['json']['preferred_chat_language']);
    }

    public function testRememberedDeviceListRequiresSessionToken(): void
    {
        $result = $this->requestChatAuthGate([
            'action' => 'listDevices', 'chatId' => 'PC-12345678', 'email' => 'customer@example.test',
        ]);
        self::assertSame(401, $result['status']);
        self::assertFalse($result['json']['success']);
    }

    public function testRememberedDeviceListReturnsOnlyDisplayFields(): void
    {
        $result = $this->requestChatAuthGate([
            'action' => 'listDevices', 'chatId' => 'PC-12345678', 'email' => 'customer@example.test',
            'sessionToken' => 'fixture-session-token',
        ]);
        self::assertSame(200, $result['status']);
        self::assertTrue($result['json']['success']);
        self::assertSame(['id' => 7, 'label' => 'This device', 'last_used_at' => '2026-11-08 12:00:00', 'created_at' => '2026-11-01 12:00:00'], $result['json']['devices'][0]);
        self::assertArrayNotHasKey('device_fingerprint', $result['json']['devices'][0]);
    }

    public function testRememberedDeviceRemovalRequiresAuthenticatedPrincipalAndId(): void
    {
        $result = $this->requestChatAuthGate([
            'action' => 'forgetDevice', 'chatId' => 'PC-12345678', 'email' => 'customer@example.test',
            'sessionToken' => 'fixture-session-token', 'deviceId' => 7,
        ]);
        self::assertSame(200, $result['status']);
        self::assertTrue($result['json']['success']);
    }

    public function testAdminTranscriptUsesMessageDirection(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/adopt/admin/index.html');
        self::assertStringContainsString("const side = msg.from_type === 'admin' ? 'admin' : 'customer';", $source);
        self::assertStringContainsString('messageId: msg.id, side })', $source);
    }

    public function testTemporaryPasswordCannotBeRedeemedAfterAtomicClaimIsLost(): void
    {
        $tempPassword = bin2hex(random_bytes(16));
        $base = $this->tmp . '/reset-fixture';
        mkdir($base . '/includes', 0775, true);
        file_put_contents($base . '/chat-password-reset.php', file_get_contents(dirname(__DIR__) . '/adopt/admin/chat-password-reset.php'));
        file_put_contents($base . '/chat-mail.php', "<?php\n");
        file_put_contents($base . '/includes/chat-auth.php', <<<'PHP'
<?php
function chatAuthClearThrottle($principalId): void {}
function verify_token($token, $principalId): bool { return false; }
function chatAuthSetSecret($principalId, $tier, $secret): array { return ['success'=>true]; }
PHP);
        file_put_contents($base . '/chat-config.php', <<<'PHP'
<?php
class Web02ResetStatement {
    private string $query;
    public function __construct(string $query) { $this->query = $query; }
    public function execute(array $params = []): bool {
        if (strpos($this->query, 'INSERT INTO chat_session_token') !== false) $GLOBALS['web02_reset_token_inserts']++;
        return true;
    }
    public function fetch() { return $GLOBALS['web02_reset_row']; }
    public function rowCount(): int { return 0; }
}
class Web02ResetDatabase { public function prepare(string $query): Web02ResetStatement { return new Web02ResetStatement($query); } }
function chatDb(): Web02ResetDatabase { static $db; return $db ?? ($db = new Web02ResetDatabase()); }
function chatJsonInput(): array { return $_POST; }
function chatCheckRateLimit(string $key, int $limit, int $window): bool { return true; }
function chatValidEmail(string $email): bool { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }
function chatRespond(array $payload, int $status = 200): void { http_response_code($status); echo json_encode($payload); exit; }
PHP);
        $statusPath = $base . '/response-status';
        $insertCountPath = $base . '/reset-token-inserts';
        $runner = $base . '/request.php';
        $row = ['id' => 1, 'principal_id' => 'PC-12345678', 'temp_hash' => password_hash($tempPassword, PASSWORD_DEFAULT)];
        $content = '<?php '
            . '$GLOBALS["web02_reset_row"] = ' . var_export($row, true) . '; '
            . '$GLOBALS["web02_reset_token_inserts"] = 0; '
            . '$_SERVER["REQUEST_METHOD"] = "POST"; '
            . '$_POST = ' . var_export(['action'=>'temporaryLogin', 'email'=>'customer@example.test', 'temporaryPassword'=>$tempPassword], true) . '; '
            . 'register_shutdown_function(static function () { file_put_contents(' . var_export($statusPath, true) . ', (string) http_response_code()); file_put_contents(' . var_export($insertCountPath, true) . ', (string) $GLOBALS["web02_reset_token_inserts"]); }); '
            . 'require ' . var_export($base . '/chat-password-reset.php', true) . ';';
        file_put_contents($runner, $content);
        $output = [];
        exec('php ' . escapeshellarg($runner) . ' 2>&1', $output, $exitCode);
        $body = implode("\n", $output);
        $json = json_decode($body, true);
        self::assertSame(0, $exitCode, $body);
        self::assertIsArray($json, $body);
        self::assertFalse($json['success']);
        self::assertSame(401, (int)file_get_contents($statusPath));
        self::assertSame('0', file_get_contents($insertCountPath), 'A reset session token must not be inserted when the atomic claim affects no row.');
    }

    /** Run one request against a copied handler and temporary fake token/data tree. */
    private function request(array $post, string $method = 'POST', string $handler = 'delete-pet.php'): array
    {
        $statusPath = $this->tmp . '/response-status';
        $runner = $this->tmp . '/request.php';
        $content = '<?php '
            . '$_SERVER["REQUEST_METHOD"] = ' . var_export($method, true) . '; '
            . '$_POST = ' . var_export($post, true) . '; '
            . 'register_shutdown_function(static function () { file_put_contents(' . var_export($statusPath, true) . ', (string) http_response_code()); }); '
            . 'require ' . var_export($this->tmp . '/adopt/admin/' . $handler, true) . ';';
        file_put_contents($runner, $content);

        $output = [];
        exec('php ' . escapeshellarg($runner) . ' 2>&1', $output, $exitCode);
        $body = implode("\n", $output);
        $json = json_decode($body, true);
        self::assertSame(0, $exitCode, $body);
        self::assertIsArray($json, $body);
        $status = is_file($statusPath) ? (int)file_get_contents($statusPath) : 0;
        if ($status < 100 || $status > 599) $status = !empty($json['success']) ? 200 : 400;
        return ['status' => $status, 'json' => $json];
    }

    /** Run the actual chat-start endpoint with fake dependencies and no DB/network access. */
    private function requestChatStart(array $post, array $thread): array
    {
        $base = $this->tmp . '/chat-fixture';
        mkdir($base . '/includes', 0775, true);
        file_put_contents($base . '/chat-start.php', file_get_contents(dirname(__DIR__) . '/adopt/admin/chat-start.php'));
        file_put_contents($base . '/chat-mail.php', "<?php function chatNotifyNewThread(array \$data): void {}\n");
        file_put_contents($base . '/includes/chat-auth.php', <<<'PHP'
<?php
function chatAuthEnsure($id): array { return ['auth_tier' => 'none']; }
function chatAuthTrustedDevice($id, $device): bool { return false; }
function issue_token($id): string { return 'fixture-session-token'; }
function chatAuthValidateSecret($tier, $secret): ?string { return null; }
function chatAuthSetSecret($id, $tier, $secret): array { return ['success' => true]; }
PHP);
        file_put_contents($base . '/chat-config.php', <<<'PHP'
<?php
if (!defined('CHAT_AUTO_RESPONSES')) define('CHAT_AUTO_RESPONSES', ['general' => 'Welcome']);
class Web02ChatStatement {
    private string $query; private array $params = [];
    public function __construct(string $query) { $this->query = $query; }
    public function execute(array $params = []): bool { $this->params = $params; return true; }
    public function fetch() {
        $thread = $GLOBALS['web02_thread'];
        if (strpos($this->query, 'WHERE chat_id = ?') !== false) return ($thread && $thread['chat_id'] === ($this->params[0] ?? null)) ? $thread : false;
        if (strpos($this->query, 'WHERE email = ?') !== false) return ($thread && strtolower($thread['email']) === strtolower((string)($this->params[0] ?? ''))) ? $thread : false;
        return false;
    }
}
class Web02ChatDatabase {
    public function prepare(string $query): Web02ChatStatement { return new Web02ChatStatement($query); }
    public function lastInsertId(): string { return '99'; }
}
function chatDb(): Web02ChatDatabase { static $db; return $db ?? ($db = new Web02ChatDatabase()); }
function chatJsonInput(): array { return $_POST; }
function chatRespond(array $payload, int $status = 200): void { http_response_code($status); echo json_encode($payload); exit; }
function chatValidEmail(string $email): bool { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }
function chatValidReason(string $reason): bool { return in_array($reason, ['adoption', 'shipping', 'health', 'general'], true); }
function chatCheckRateLimit(string $key, int $limit, int $window): bool { return true; }
function chatGenerateChatId(): string { return 'PC-NEW0001'; }
function chatGetBotProfile(): array { return ['active' => false, 'welcomeMessages' => [], 'welcomeFallback' => null]; }
function chatBotSubstitute(string $text, string $name): string { return $text; }
PHP);
        $statusPath = $base . '/response-status';
        $runner = $base . '/request.php';
        $content = '<?php '
            . '$GLOBALS["web02_thread"] = ' . var_export($thread, true) . '; '
            . '$_SERVER["REQUEST_METHOD"] = "POST"; '
            . '$_SERVER["REMOTE_ADDR"] = "127.0.0.1"; '
            . '$_POST = ' . var_export($post, true) . '; '
            . 'register_shutdown_function(static function () { file_put_contents(' . var_export($statusPath, true) . ', (string) http_response_code()); }); '
            . 'require ' . var_export($base . '/chat-start.php', true) . ';';
        file_put_contents($runner, $content);
        $output = [];
        exec('php ' . escapeshellarg($runner) . ' 2>&1', $output, $exitCode);
        $body = implode("\n", $output);
        $json = json_decode($body, true);
        self::assertSame(0, $exitCode, $body);
        self::assertIsArray($json, $body);
        $status = is_file($statusPath) ? (int)file_get_contents($statusPath) : 0;
        if ($status < 100 || $status > 599) $status = !empty($json['success']) ? 200 : 400;
        return ['status' => $status, 'json' => $json];
    }

    /** Run the existing chat-auth-gate route with fake DB/auth dependencies only. */
    private function requestChatAuthGate(array $post): array
    {
        $base = $this->tmp . '/auth-gate-fixture';
        mkdir($base . '/includes', 0775, true);
        file_put_contents($base . '/chat-auth-gate.php', file_get_contents(dirname(__DIR__) . '/adopt/admin/chat-auth-gate.php'));
        file_put_contents($base . '/includes/chat-auth.php', <<<'PHP'
<?php
function chatRequireSessionToken($id, $token = null): void { if ($token !== 'fixture-session-token') chatRespond(['success'=>false,'error'=>'A valid chat session is required.'],401); }
function chatAuthEnsure($id): array { return ['auth_tier'=>'none']; }
function chatAuthTrustedDevice($id, $device): bool { return false; }
function issue_token($id): string { return 'fixture-session-token'; }
function verify_token($token, $id): bool { return $token === 'fixture-session-token'; }
function log_audit($id, $endpoint, $result): void {}
PHP);
        file_put_contents($base . '/chat-config.php', <<<'PHP'
<?php
class Web02DeviceStatement {
    private string $query; private array $params=[];
    public function __construct(string $query) { $this->query=$query; }
    public function execute(array $params=[]): bool { $this->params=$params; return true; }
    public function fetch() { return $GLOBALS['web02_device_thread']; }
    public function fetchAll(int $mode=PDO::FETCH_DEFAULT): array { return $GLOBALS['web02_devices']; }
    public function rowCount(): int { return strpos($this->query,'DELETE FROM chat_remember_device')!==false && count($this->params)===2 && $this->params[0]===7 && $this->params[1]==='PC-12345678' ? 1 : 0; }
}
class Web02DeviceDatabase { public function prepare(string $query): Web02DeviceStatement { return new Web02DeviceStatement($query); } }
function chatDb(): Web02DeviceDatabase { static $db; return $db ?? ($db=new Web02DeviceDatabase()); }
function chatJsonInput(): array { return $_POST; }
function chatRespond(array $payload,int $status=200): void { http_response_code($status); echo json_encode($payload); exit; }
function chatValidEmail(string $email): bool { return filter_var($email,FILTER_VALIDATE_EMAIL)!==false; }
PHP);
        $statusPath = $base . '/response-status';
        $runner = $base . '/request.php';
        $devices = [[
            'id'=>7, 'label'=>'This device', 'last_used_at'=>'2026-11-08 12:00:00', 'created_at'=>'2026-11-01 12:00:00',
        ]];
        $content = '<?php '
            . '$GLOBALS["web02_device_thread"] = ' . var_export($this->threadFixture(), true) . '; '
            . '$GLOBALS["web02_devices"] = ' . var_export($devices, true) . '; '
            . '$_SERVER["REQUEST_METHOD"] = "POST"; $_POST = ' . var_export($post, true) . '; '
            . 'register_shutdown_function(static function () { file_put_contents(' . var_export($statusPath, true) . ', (string) http_response_code()); }); '
            . 'require ' . var_export($base . '/chat-auth-gate.php', true) . ';';
        file_put_contents($runner, $content);
        $output = [];
        exec('php ' . escapeshellarg($runner) . ' 2>&1', $output, $exitCode);
        $body = implode("\n", $output);
        $json = json_decode($body, true);
        self::assertSame(0, $exitCode, $body);
        self::assertIsArray($json, $body);
        $status = is_file($statusPath) ? (int)file_get_contents($statusPath) : 0;
        if ($status < 100 || $status > 599) $status = !empty($json['success']) ? 200 : 400;
        return ['status' => $status, 'json' => $json];
    }

    private function threadFixture(): array
    {
        return [
            'id' => 1, 'chat_id' => 'PC-12345678', 'email' => 'customer@example.test',
            'full_name' => 'Customer', 'reason' => 'general', 'status' => 'active',
            'preferred_chat_language' => 'fr',
        ];
    }

    private function replaceDogsWithCategorySymlink(): void
    {
        $dogs = $this->tmp . '/adopt/adoptions/pets/dogs';
        $cats = $this->tmp . '/adopt/adoptions/pets/cats';
        $this->removeTree($dogs);
        mkdir($cats, 0775, true);
        self::assertTrue(symlink($cats, $dogs));
    }

    /** Remove only this test's temporary files; never follow symlinks. */
    private function removeTree(string $path): void
    {
        if (!file_exists($path) && !is_link($path)) return;
        if (is_link($path) || is_file($path)) {
            @unlink($path);
            return;
        }
        foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $item) {
            $this->removeTree($item->getPathname());
        }
        @rmdir($path);
    }
}
// TASK-WEB-02-ADMIN-BASELINE END
