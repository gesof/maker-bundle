<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Symfony\Bundle\FrameworkBundle\Controller\<?= $parent_class_name ?>;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
<?php if ($format === 'annotation'): ?>
	use Symfony\Component\Routing\Annotation\Route;
<?php endif ?>

/**
*
<?php if ($format === 'annotation'): ?>
* @Route("<?= $route_path ?>")
<?php endif ?>
*/
class <?= $class_name ?> extends <?= $parent_class_name; ?><?= "\n" ?>
{
    /**
<?php if ($format === 'annotation'): ?>
	* @Route("/", name="<?= $route_name ?>_index", methods="GET")
<?php endif ?>
    * @param Request $request
    * @return Response
    */
    public function indexAction(Request $request): Response
    {
        return $this->render('<?= $templates_path ?>/index.html.twig', [

        ]);
    }
}