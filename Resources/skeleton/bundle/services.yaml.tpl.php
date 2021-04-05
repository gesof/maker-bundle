services:
    <?= $controller_full_class_name ?>:
        tags: ['controller.service_arguments']
        calls:
            - setContainer: [ '@service_container' ]

#    <?= $extension_alias ?>.example:
#        class: <?= $namespace ?>\Services\Example
#        arguments: ["@service_id", "plain_value", "%parameter%"]