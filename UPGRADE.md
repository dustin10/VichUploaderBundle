Upgrading from v0.11.0 to master
================================

Nothing!

Upgrading from v0.10.0 to v0.11.0
================================

- Symfony versions prior to 2.3 are not supported anymore.

Upgrading from v0.9.0 to v0.10.0
================================

- `inject_on_load` config param defaults to false. Set it to
true if you want to keep your old behavior.

- the `NamerInterface` and `DirectoryNamerInterface` were modified.

- the `resolvePath` and `resolveUri` Storage methods now take a mapping name
  instead of a field name. The same goes for the UploaderExtension and
  UploaderHelper `asset` method.

Upgrading from v0.5.0 to 0.6.0
===============================

- `getUriPrefix` default value is now /uploads

- `delete_on_update` config param added. It defaults to true.

Upgrading from v0.4.0 to v0.5.0
===============================

- gaufrette and KnpGaufretteBundle are now soft dependencies

- `storage` configuration entry added.

- `web_dir_name` configuration entry deprecated.

- `mapping`:`upload_dir` configuration entry renamed to `mapping`:`upload_destination`

- `mapping`:`uri_prefix` configuration entry added

- The old `vich_uploader.uploader` service has been split into two new
services for a more modular, separation of concerns approach. The new services are
`vich_uploader.storage.gaufrette` and `vich_uploader.file_injector`. The storage
service is responsible for saving and removing files as well as resolving their path.
The injector service is responsible for injecting `File` instances back into the
object upon retrieval from the datastore.


Upgrading from v0.2.0 to v0.3.0
===============================

- Annotations have changed namespace from `Vich\UploaderBundle\Annotation` to
`Vich\UploaderBundle\Mapping\Annotation`. You will need to update  your `use`
statement for the annotations used in your entity or document classes.

- The `mappings` configuration entry prototype has had a new option added to it.
The `inject_on_load` config option specifies whether or not the uploadable fields
should have an instnace of `Symfony\Component\HttpFoundation\File\File` created
and injected into the property upon retrieval from the datastore. THis option has a
default value of `true`.
