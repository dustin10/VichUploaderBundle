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

- The old `vich_uploader.uploader` service has been split into two new
services for a more modular, separation of concerns approach. The new services are
`vich_uploader.storage.file_system` and `vich_uploader.file_injector`. The storage
service is responsible for saving and removing files as well as resolving their path.
The injector service is responsible for injecting `File` instances back into the
object upon retrieval from the datastore.
