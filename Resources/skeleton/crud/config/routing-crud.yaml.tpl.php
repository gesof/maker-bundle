<?= $route_name ?>_index:
    path: /
    defaults: { _controller: "<?= sprintf('%s:%s:%s', $bundle_name, $entity, 'index') ?>" }
    methods: GET
    
<?= $route_name ?>_show:
    path: /{<?= $entity_identifier ?>}/show
    defaults: { _controller: "<?= sprintf('%s:%s:%s', $bundle_name, $entity, 'show') ?>" }
    methods: GET
    
<?= $route_name ?>_new:
    path: /new
    defaults: { _controller: "<?= sprintf('%s:%s:%s', $bundle_name, $entity, 'new') ?>" }
    methods: [GET, POST]
    
<?= $route_name ?>_edit:
    path: /{<?= $entity_identifier ?>}/edit
    defaults: { _controller: "<?= sprintf('%s:%s:%s', $bundle_name, $entity, 'edit') ?>" }
    methods: [GET, POST]
    
<?= $route_name ?>_delete:
    path: /{<?= $entity_identifier ?>}/delete
    defaults: { _controller: "<?= sprintf('%s:%s:%s', $bundle_name, $entity, 'delete') ?>" }
    methods: DELETE
    