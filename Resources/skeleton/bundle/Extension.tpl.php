<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class <?= $class_name ?> extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

<?php if ($format === 'yaml' || $format === 'annotation'): ?>
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
<?php endif ?>
<?php if ($format === 'xml'): ?>
            $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('services.xml');
<?php endif ?>
<?php if ($format === 'php'): ?>
            $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('services.php');
<?php endif ?>
    }
}