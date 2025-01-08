<?php

namespace MainGPT;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class App extends AbstractActionable
{
    use HookableTrait;

    public const ID = 'main';
    public const CONFIG_DIR = 'etc';
    public const DEFAULT_SERVICE_XML_FILE = 'services.xml';
    public const INIT_LIST_PARAMS = 'app.init_list';
    public const TEXT_DOMAIN = self::ID . '-locale';
    public const INIT_NAME = 'init';

    protected ContainerInterface $container;

    /**
     * @throws Exception
     */
    public function __construct(string $basePath)
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new XmlFileLoader(
            $containerBuilder,
            new FileLocator($basePath . DIRECTORY_SEPARATOR . self::CONFIG_DIR)
        );
        $loader->load(self::DEFAULT_SERVICE_XML_FILE);
        $this->container = $containerBuilder;
        $this->container->setParameter('app.base_path', $basePath);
        $this->init();
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $initList = $this->container->getParameter(self::INIT_LIST_PARAMS);
        foreach ($initList as $init) {
            /** @var InitableInterface $obj */
            $obj = $this->container->get($init);
            $obj->init();
        }
    }
}
