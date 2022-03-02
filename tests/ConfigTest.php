<?php

use Storekeeper\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    protected static string $configFilename = 'testConf';
    protected string $configFilepath;
    protected static string $separator = '.';

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->configFilepath =
            __DIR__ . DIRECTORY_SEPARATOR
            . 'configs' . DIRECTORY_SEPARATOR
            . self::$configFilename .'.php';
    }

    protected function randString(int $length): string
    {
        return substr(str_shuffle('abcdefghijklmonpqrstuvwxyz123456789'), 0, $length);
    }

    protected function getAccessibleProperty($objectOrClass, string $propertyName)
    {
        $reflection = new ReflectionClass($objectOrClass);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

    /**
     * @param object|string $objectOrClass
     * @param string $propertyName
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    protected function getConfigAccessibleProperty(string $propertyName)
    {
        return $this->getAccessibleProperty(Config::class, $propertyName);
    }

    public function setupConfig()
    {
        Config::setConfigFolder(__DIR__ . DIRECTORY_SEPARATOR . 'configs');
        Config::setSeparator(static::$separator);

        $reflection = new ReflectionClass($this->getTestConfigClass());
        $reflection->setStaticPropertyValue('configs', []);
    }

    public function getTestConfig()
    {
        return require $this->configFilepath;
    }

    public function getTestConfigClass(): Config
    {
        return new Config(static::$configFilename);
    }

    public function createConfig()
    {
        $data = '<?php' . PHP_EOL;
        $data .= 'return [' . PHP_EOL;
        $data .= "    'string' => 'value1'," . PHP_EOL;
        $data .= "    'array' => [" . PHP_EOL;
        $data .= "        'index1' => 'value1'," . PHP_EOL;
        $data .= "        'subarray' => [" . PHP_EOL;
        $data .= "            'index1' => 'value1'" . PHP_EOL;
        $data .= "        ]," . PHP_EOL;
        $data .= "    ]," . PHP_EOL;
        $data .= "    'boolean' => true" . PHP_EOL;
        $data .= "];" . PHP_EOL;

        file_put_contents($this->configFilepath, $data);
    }

    public function unlinkConfig()
    {
        unlink($this->configFilepath);
    }

    public function configExists(): bool
    {
        return file_exists($this->configFilepath);
    }

    public function configEmpty(): bool
    {
        return $this->getTestConfig() === [];
    }

    //!!TESTS STARTS HERE!!\\
    public function test_setConfigFolder()
    {
        $this->setupConfig();
        $configFolder = $this->getConfigAccessibleProperty('configFolder');

        // path without '/' at the end
        $pathBadEnd = DIRECTORY_SEPARATOR . $this->randString(3) . DIRECTORY_SEPARATOR
            . $this->randString(3) . DIRECTORY_SEPARATOR
            . $this->randString(3) . DIRECTORY_SEPARATOR
            . $this->randString(3);
        $path = $pathBadEnd . DIRECTORY_SEPARATOR;

        Config::setConfigFolder($pathBadEnd);
        $this->assertSame($pathBadEnd . DIRECTORY_SEPARATOR, $configFolder->getValue());

        Config::setConfigFolder($path);
        $this->assertSame($path, $configFolder->getValue());
    }

    public function test_setSeparator()
    {
        $separator = $this->getConfigAccessibleProperty('separator');
        $randChar = $this->randString(1);

        // Default
        $this->assertSame('.', $separator->getValue());

        Config::setSeparator($randChar);
        $this->assertSame($randChar, $separator->getValue());
    }

    public function test_get()
    {
        $this->setupConfig();

        if (!$this->configExists() || $this->configEmpty()) $this->createConfig();
        $testConfig = $this->getTestConfig();
        $config = new Config(static::$configFilename);

        $this->assertSame($testConfig['string'], $config->get('string'));
        $this->assertSame($testConfig['array']['index1'], $config->get('array.index1'));
        $this->assertSame($testConfig['array']['subarray']['index1'], $config->get('array.subarray.index1'));
        $this->assertSame($testConfig['boolean'], $config->get('boolean'));
    }

    public function test_unset()
    {
        $this->setupConfig();
        $config = new Config(static::$configFilename);

        $this->assertArrayHasKey('string', $config->get());
        $this->assertArrayHasKey('index1', $config->get()['array']);
        $this->assertArrayHasKey('index1', $config->get()['array']['subarray']);

        $config->unset('array.index1');
        $config->unset('array.subarray.index1');
        $config->unset('string');

        $this->assertArrayNotHasKey('string', $config->get());
        $this->assertArrayNotHasKey('index1', $config->get()['array']);
        $this->assertArrayNotHasKey('index1', $config->get()['array']['subarray']);
    }

    public function test_delete()
    {
        if (!file_exists($this->configFilepath)) $this->createConfig();

        $this->setupConfig();
        $config = new Config(static::$configFilename);

        $this->assertFileExists($this->configFilepath);

        $config->delete();
        $this->assertFileDoesNotExist($this->configFilepath);

        $this->createConfig();
    }

    public function test_isEmpty()
    {
        $this->setupConfig();
        if ($this->configExists()) $this->unlinkConfig();

        $config = $this->getTestConfigClass();
        $this->assertTrue($config->isEmpty());

        file_put_contents($this->configFilepath, '<?php return [];');
        $this->assertTrue($config->isEmpty());

        $this->unlinkConfig();
    }

    public function test_isNotExists()
    {
        $this->setupConfig();
        $this->assertSame(!$this->configExists(), $this->getTestConfigClass()->isNotExists());
    }

    public function test_contains()
    {
        $this->setupConfig();

        if (!$this->configExists() || $this->configEmpty()) {
            $this->createConfig();
        }

        $config = $this->getTestConfigClass();

        // Contains
        $this->assertTrue($config->contains('string'));
        $this->assertTrue($config->contains('array.index1'));
        $this->assertTrue($config->contains('array.subarray.index1'));
        $this->assertTrue($config->contains('boolean'));

        // Not contains
        $this->assertFalse($config->contains('array.subarray.ind'));
        $this->assertFalse($config->contains('array.subarrayas'));
        $this->assertFalse($config->contains('string.thisvalueisnotexists'));
        $this->assertFalse($config->contains('array.arrasdasdaskldfgkxcnjeqwhr'));
    }
}
