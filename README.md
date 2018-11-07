# GesofMakerBundle
This bundle is a **code generation tool** for Symfony 4 that extends the capabilities of symfony/maker-bundle by adding the posibility to use **roles** and generate **yaml** routing files.
IMPORTANT: this package is bundle oriented allowing one to generate files based on the directory structure used by previous Symfony versions (^2.x | ^3.x).

Generate doctrine entity CRUD using interactive mode:
```
$ php bin/console gesof:make:crud
```
or use predefined arguments/options
```
$ php bin/console gesof:make:crud Acme\\DemoBundle\\Entity\\Company --roles=ROLE_USER --format=yaml
```
### Arguments:
`entity-class`
Doctrine entity class to generate CRUD for.
### Options
`--roles`
Roles to secure the routes with.
```
... --roles=ROLE_USER --roles=ROLE_ADMIN
```
Multiple roles are accepted.

`--format`
Routing format: yaml or annotation

This tools uses a predefined sets of templates to generate the target files (controllers, forms etc). 
If you wish to override the base templates, you need to copy the skeleton directory from `vendors/gesof/maker-bundle/Resources/skeleton` and specify the new path as below:
```
# config/packages/gesof_maker.yaml
gesof_maker:
    skeleton_dir: '%kernel.project_dir%/src/Acme/DemoBundle/Resources/skeleton'
```