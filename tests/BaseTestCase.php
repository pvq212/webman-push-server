<?php
/**
 * This file is part of workbunny.
 *
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    chaz6chez<chaz6chez1993@outlook.com>
 * @copyright chaz6chez<chaz6chez1993@outlook.com>
 * @link      https://github.com/workbunny/webman-push-server
 * @license   https://github.com/workbunny/webman-push-server/blob/main/LICENSE
 */
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Webman\Bootstrap;
use Webman\Config;
use Webman\Route;
use Workbunny\WebmanPushServer\ApiServer;
use Workbunny\WebmanPushServer\PushServer;


abstract class BaseTestCase extends TestCase
{
    /** @var string  */
    protected string $_websocket_header = "GET /app/workbunny?protocol=7&client=js&version=3.2.4&flash=false HTTP/1.1\r\nConnection: Upgrade\r\nUpgrade: websocket\r\n\r\n";
    /** @var PushServer|null  */
    protected static ?PushServer $pushServer = null;
    /** @var ApiServer|null  */
    protected static ?ApiServer $apiServer = null;
    /** @var bool  */
    protected static bool $init = false;

    /**
     * @return string
     */
    public function getWebsocketHeader(): string
    {
        return $this->_websocket_header;
    }

    /**
     * @return PushServer|null
     */
    public function getPushServer(): ?PushServer
    {
        return static::$pushServer;
    }

    /**
     * @return ApiServer|null
     */
    public function getApiServer(): ?ApiServer
    {
        return static::$apiServer;
    }

    /** @inheritdoc  */
    public static function setUpBeforeClass(): void
    {
        // 模拟webman-framework加载框架
        require_once __DIR__ . '/../vendor/workerman/webman-framework/src/support/helpers.php';
        if (!static::$init) {
            static::$init = true;
            // 模拟webman-framework加载配置
            Config::load($configPath = __DIR__ . '/../src/config', ['route']);
            // 模拟webman-framework加载bootsrap
            foreach (\config('plugin.workbunny.webman-push-server.bootstrap') as $bootstrap) {
                if (
                    class_exists($bootstrap) and
                    is_a($bootstrap, Bootstrap::class, true)
                ) {
                    $bootstrap::start(null, $configPath);
                }
            }
        }
        // 取消workerman Timer中存在的pcntl_alarm(1)，避免测试用例无法运行完整
        pcntl_alarm(0);

        // 模拟启动服务
        static::$pushServer = new PushServer();
        static::$apiServer = new ApiServer();
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
    }

    /** @inheritdoc  */
    protected function tearDown(): void
    {
        ApiServer::setConnections([]);
        PushServer::setConnections([]);
        $this->getPushServer()->setLastEvent(null);
        parent::setUp();
    }
}
