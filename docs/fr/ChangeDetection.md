### Snapshots et Détection des Changements

#### Snapshots
Un snapshot capture l'état d'une entité lorsqu'elle est initialement gérée. Ce snapshot est utilisé pour détecter les changements.

#### Détection des Changements
Les changements sont détectés automatiquement avant l'appel à `flush`. Seuls les champs modifiés sont inclus dans l'opération de mise à jour.

--- 

[Retour](./FEATURES_DOCUMENTATION.md)