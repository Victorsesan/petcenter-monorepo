<?php
// TASK-WEB-02-ADMIN-BASELINE START — exercise the pet-delete handler only against disposable fixtures.
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
        foreach (['delete-pet.php', 'get-pets.php', 'publish-pet.php', 'delete-application.php'] as $handler) {
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
        $status = is_file($statusPath) ? (int)file_get_contents($statusPath) : 200;
        return ['status' => $status, 'json' => $json];
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
