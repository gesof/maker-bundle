<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder('<?= $extension_alias ?>');
		$rootNode = $treeBuilder->getRootNode();

		return $treeBuilder;
	}
}