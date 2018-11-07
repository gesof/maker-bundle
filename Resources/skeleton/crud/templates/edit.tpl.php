<?= $helper->getHeadPrintCode('Edit '.$entity_class_name) ?>

{% block body %}
    <h1>Edit <?= $entity_class_name ?></h1>

    {{ include('<?= $bundle_name ?>:<?= $entity_class_name ?>:_form.html.twig', {'button_label': 'Update'}) }}

    <a href="{{ path('<?= $route_name ?>_index') }}">back to list</a>

    {{ include('<?= $bundle_name ?>:<?= $entity_class_name ?>:_delete_form.html.twig') }}
{% endblock %}
