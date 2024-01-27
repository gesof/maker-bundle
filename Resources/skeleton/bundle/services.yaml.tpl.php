services:
    <?= $controller_full_class_name ?>:
        resource: '../../Controller/*'
        autowire: true
        autoconfigure: true

#    <?= $extension_alias ?>.example:
#        class: <?= $namespace ?>\Services\Example
#        arguments: ["@service_id", "plain_value", "%parameter%"]
