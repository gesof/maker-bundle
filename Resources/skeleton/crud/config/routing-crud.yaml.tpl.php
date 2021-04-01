<?= $route_name ?>_index:
    path: /
    controller: <?= sprintf('%s:%s', $controller_full_class_name, 'indexAction') ?><?= "\n" ?>
    methods: GET
    
<?= $route_name ?>_show:
    path: /{<?= $entity_identifier ?>}/show
    controller: <?= sprintf('%s:%s', $controller_full_class_name, 'showAction') ?><?= "\n" ?>
    methods: GET
    
<?= $route_name ?>_new:
    path: /new
    controller: <?= sprintf('%s:%s', $controller_full_class_name, 'newAction') ?><?= "\n" ?>
    methods: [GET, POST]
    
<?= $route_name ?>_edit:
    path: /{<?= $entity_identifier ?>}/edit
    controller: <?= sprintf('%s:%s', $controller_full_class_name, 'editAction') ?><?= "\n" ?>
    methods: [GET, POST]
    
<?= $route_name ?>_delete:
    path: /{<?= $entity_identifier ?>}/delete
    controller: <?= sprintf('%s:%s', $controller_full_class_name, 'deleteAction') ?><?= "\n" ?>
    methods: DELETE
    