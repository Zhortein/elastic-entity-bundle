### Gestion des Métadonnées et Mise en Cache

#### Description
Le `MetadataCollector` est une composante essentielle du bundle, responsable de la collecte et de la mise en cache des métadonnées associées aux entités `ElasticEntity`. Il garantit une performance optimale en réduisant les opérations de réflexion à un minimum.

---

#### Fonctionnalités

1. **Ajout de Métadonnées** : Le `MetadataCollector` peut ajouter des métadonnées pour une classe donnée.
   ```php
   $reflectionClass = new \ReflectionClass(Product::class);
   $metadataCollector->addMetadata($reflectionClass);
   ```

2. **Récupération des Métadonnées** : Les métadonnées d’une classe peuvent être récupérées facilement.
   ```php
   $metadata = $metadataCollector->getMetadata(Product::class);
   ```

3. **Métadonnées Dynamiques** : Charge dynamiquement les métadonnées au besoin.
   ```php
   if ($metadataCollector->getMetadata(Order::class) === null) {
       $metadataCollector->addMetadata(new \ReflectionClass(Order::class));
   }
   ```

4. **Effacement du Cache** : Permet de vider le cache des métadonnées.
   ```php
   $metadataCollector->clearMetadata();
   ```

---

#### Exemple d’Utilisation

##### Ajout et Récupération des Métadonnées

```php
use Zhortein\ElasticEntityBundle\Metadata\MetadataCollector;

$reflectionClass = new \ReflectionClass(Product::class);
$metadataCollector = new MetadataCollector($cacheInterface);

// Ajouter des métadonnées
$metadataCollector->addMetadata($reflectionClass);

// Récupérer des métadonnées
$metadata = $metadataCollector->getMetadata(Product::class);
if ($metadata) {
    echo "Class: " . $metadata['class'];
}
```

##### Effacer le Cache

```php
$metadataCollector->clearMetadata();
```

---

#### Configuration du Cache

Le `MetadataCollector` utilise le composant [Cache](https://symfony.com/doc/current/components/cache.html) de Symfony. Vous pouvez spécifier le service de cache à utiliser via l’injection de dépendance.

##### Exemple de Configuration

Dans le fichier `services.yaml` :

```yaml
services:
    Zhortein\ElasticEntityBundle\Metadata\MetadataCollector:
        arguments:
            $cache: '@cache.app'
```

--- 

[Retour](./FEATURES_DOCUMENTATION.md)