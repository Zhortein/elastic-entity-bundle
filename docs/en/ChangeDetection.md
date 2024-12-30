### Snapshots and Change Detection

#### Snapshots
A snapshot captures the state of an entity when it is first managed. This snapshot is used to detect changes.

#### Change Detection
Changes are detected automatically before `flush` is called. Only modified fields are included in the update operation.

---

[Back](./FEATURES_DOCUMENTATION.md)
