# UPGRADE FROM `v1.0.0-RC.3` TO `v1.0.0-RC.4`

1. `Doctrine\ORM\EntityManagerInterface` has been added as a last argument to constructor for `CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProvider`.

# UPGRADE FROM `v1.0.0-RC.2` TO `v1.0.0-RC.3`

1. The namespace of DoctrineMigrations has changed. Mark all the plugin's migrations as executed:

   ```bash
   bin/console doctrine:migrations:version "CommerceWeavers\SyliusSaferpayPlugin\Migrations\Version20230424115143" --add --no-interaction
   bin/console doctrine:migrations:version "CommerceWeavers\SyliusSaferpayPlugin\Migrations\Version20230506091600" --add --no-interaction
   ```
