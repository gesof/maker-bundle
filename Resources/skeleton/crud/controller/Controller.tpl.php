<?= "<?php\n" ?>
<?php
    $security_expression = !empty($roles) ? implode(' or ', array_map(function($role) {
        return sprintf('is_granted(\'%s\')', $role);
    }, $roles)) : NULL;
?>

namespace <?= $namespace ?>;

use Doctrine\ORM\EntityManagerInterface;
use <?= $entity_full_class_name ?>;
use <?= $form_full_class_name ?>;
<?php if (isset($repository_full_class_name)): ?>
use <?= $repository_full_class_name ?>;
<?php endif ?>
use Symfony\Bundle\FrameworkBundle\Controller\<?= $parent_class_name ?>;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
<?php if ($format === 'annotation'): ?>
use Symfony\Component\Routing\Annotation\Route;
<?php endif ?>
<?php if (!empty($roles)): ?>
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
<?php endif ?>

/**
 *
<?php if ($format === 'annotation'): ?>
 * @Route("<?= $route_path ?>")
<?php endif ?>
 */
class <?= $class_name ?> extends <?= $parent_class_name; ?><?= "\n" ?>
{
    public function __construct(protected EntityManagerInterface $em) {}

    /**
<?php if ($format === 'annotation'): ?>
     * @Route("/", name="<?= $route_name ?>_index", methods="GET")
<?php endif ?>
<?php if (!empty($roles)): ?>
     * @Security("<?= $security_expression ?>")
<?php endif ?>
     */
    public function indexAction(): Response
    {
        $repo = $this->em->getRepository(<?= $entity_class_name ?>::class);

        $<?= $entity_var_plural ?> = $repo->findAll();

        return $this->render('<?= $templates_path ?>/index.html.twig', ['<?= $entity_twig_var_plural ?>' => $<?= $entity_var_plural ?>]);
    }

    /**
<?php if ($format === 'annotation'): ?>
     * @Route("/new", name="<?= $route_name ?>_new", methods="GET|POST")
<?php endif ?>
<?php if (!empty($roles)): ?>
     * @Security("<?= $security_expression ?>")
<?php endif ?>
     */
    public function newAction(Request $request): Response
    {
        $<?= $entity_var_singular ?> = new <?= $entity_class_name ?>();
        $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>, array(
            'method' => 'POST',
            'action' => $this->generateUrl('<?= $route_name ?>_new')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($<?= $entity_var_singular ?>);
            $this->em->flush();

            return $this->redirectToRoute('<?= $route_name ?>_index');
        }

        return $this->render('<?= $templates_path ?>/new.html.twig', [
            '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
            'form' => $form->createView(),
        ]);
    }

    /**
<?php if ($format === 'annotation'): ?>
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_show", methods="GET")
<?php endif ?>
<?php if (!empty($roles)): ?>
     * @Security("<?= $security_expression ?>")
<?php endif ?>
     */
    public function showAction($<?= $entity_identifier ?>): Response
    {
        $repo = $this->em->getRepository(<?= $entity_class_name ?>::class);

        $<?= $entity_var_singular ?> = $repo->find($<?= $entity_identifier ?>);

        if (!$<?= $entity_var_singular ?>) {
            throw $this->createNotFoundException('Record not found');
        }

        return $this->render('<?= $templates_path ?>/show.html.twig', ['<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>]);
    }

    /**
<?php if ($format === 'annotation'): ?>
     * @Route("/{<?= $entity_identifier ?>}/edit", name="<?= $route_name ?>_edit", methods="GET|POST")
<?php endif ?>
<?php if (!empty($roles)): ?>
     * @Security("<?= $security_expression ?>")
<?php endif ?>
     */
    public function editAction(Request $request, $<?= $entity_identifier ?>): Response
    {
        $repo = $this->em->getRepository(<?= $entity_class_name ?>::class);

        $<?= $entity_var_singular ?> = $repo->find($<?= $entity_identifier ?>);

        if (!$<?= $entity_var_singular ?>) {
            throw $this->createNotFoundException('Record not found');
        }

        $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>, array(
            'method' => 'POST',
            'action' => $this->generateUrl('<?= $route_name ?>_edit', array(
                '<?= $entity_identifier ?>' => $<?= $entity_identifier ?>
            ))
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('<?= $route_name ?>_edit', ['<?= $entity_identifier ?>' => $<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>()]);
        }

        return $this->render('<?= $templates_path ?>/edit.html.twig', [
            '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
            'form' => $form->createView(),
        ]);
    }

    /**
<?php if ($format === 'annotation'): ?>
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_delete", methods="DELETE")
<?php endif ?>
<?php if (!empty($roles)): ?>
     * @Security("<?= $security_expression ?>")
<?php endif ?>
     */
    public function deleteAction(Request $request, $<?= $entity_identifier ?>): Response
    {
        $repo = $this->em->getRepository(<?= $entity_class_name ?>::class);

        $<?= $entity_var_singular ?> = $repo->find($<?= $entity_identifier ?>);

        if (!$<?= $entity_var_singular ?>) {
            throw $this->createNotFoundException('Record not found');
        }

        if ($this->isCsrfTokenValid('delete'.$<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>(), $request->request->get('_token'))) {
            $this->em->remove($<?= $entity_var_singular ?>);
            $this->em->flush();
        }

        return $this->redirectToRoute('<?= $route_name ?>_index');
    }
}
