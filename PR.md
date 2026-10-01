# Support uploadable fields in Doctrine ORM embeddables

Fixes #866, following the discussion in #350.

Uploadable fields can live inside Doctrine ORM embeddables, including embeddables nested in other
embeddables. They are registered on the entity under their dotted property path:

```twig
{{ vich_uploader_asset(promotion, 'images.banners.largeRectangle') }}
```

![vich_uploader_asset](https://github.com/user-attachments/assets/7e1fdaea-10c7-4be9-a9a1-fe3e5f3fd110)

## How it works

- `DoctrineEmbeddedDriver` wraps the metadata driver chain. For each embeddable in an entity's ORM
  metadata, nested ones included, it loads the embeddable's own uploadable fields from whichever driver
  maps it (attributes, YAML or XML, parent classes included) and adds them to the entity with every
  mapped property prefixed by the embedded path: `images.thumbnail` with
  `fileNameProperty: images.thumbnailName`, `size: images.thumbnailSize`, ... The existing drivers are
  unchanged.
- `VichFileType` and `VichImageType` walk up the form tree to the first parent whose data has the dotted
  field in its Vich metadata, and use that object and field name for the URI, download link and delete
  checkbox. Form types need no changes; nest the embeddable's form type as usual.
- `PropertyMapping` treats a `null` embeddable on the path as an empty field instead of throwing.

The embeddable carries `#[Vich\Uploadable]` (or a YAML/XML mapping); the entity needs no mapping of its
own. Embeddables are not uploadable on their own. The `Vich\UploaderBundle\Entity\File` embeddable works
inside an embeddable too. Docs: `docs/embeddables.md`.

The protected `VichFileType::resolveUriOption()` and `resolveDownloadLabel()` take the resolved field
name instead of the form, which affects subclasses overriding them.

## Usage rule

Doctrine only flushes when a mapped column changes, the same rule as for plain entities (#297). The
columns of the embeddables count as the entity's own, so any mapped column on the entity or anywhere
in the embeddable chain will do. A column in the embeddable itself is usually the one its setter can
reach, since the embeddable has no reference to the entity.

## Tested

- Unit tests for the driver against a real ORM `EntityManager` (nested embeddables, prefixed
  properties, a parent class mapped by another driver, embeddables not uploadable on their own, class
  listing), the form type (root entity resolution, download label, delete, fallback without an
  uploadable ancestor) and `PropertyMapping` (null embeddable).
- [Demo project](https://github.com/jennevdmeer/vich-uploader-embeddable-test) on Symfony 8.1:
  create, replace, delete, entity removal, `Entity\File` metadata (name, size, mime type, original
  name, dimensions), profiler mapping panel, `vich:mapping:debug-class` and `vich:cleanup`.

![formtype](https://github.com/user-attachments/assets/245d28c2-abe4-484b-9ca7-6736c88e04e2)
![profiler](https://github.com/user-attachments/assets/9b08ca95-af19-429f-9bfe-4de1f4ee9e55)

## Out of scope

- MongoDB ODM embedded documents: the driver reads ORM metadata only.

## Questions

1. Is the dotted path acceptable as the public field name for `vich_uploader_asset`, the mapping
   collector and the commands?
2. Is wrapping the driver chain the right place for this, or would you rather see it elsewhere in the
   metadata pipeline?
3. Should the protected `VichFileType` helper signatures change in this PR, or keep the `FormInterface`
   parameter for BC?

   The helpers only use the form to derive the field name, which for an embedded field has to be the
   dotted path on the entity found by walking up the form tree. Passing the resolved field name runs
   that walk once per field and keeps the object and field name together; keeping the form means
   every helper repeats the walk. `master` targets 3.1, so changing the signatures breaks subclasses
   overriding them in a minor release. If that is not acceptable I will switch to keeping the
   `FormInterface` parameter.

   Before:

   https://github.com/dustin10/VichUploaderBundle/blob/875416c6b8be4e143aad065c31a2fb5829d126e3/src/Form/Type/VichFileType.php#L146

   https://github.com/dustin10/VichUploaderBundle/blob/875416c6b8be4e143aad065c31a2fb5829d126e3/src/Form/Type/VichFileType.php#L159

   After:

   https://github.com/dustin10/VichUploaderBundle/blob/42131d64c29430666a811953e2ebe6411cf86ed1/src/Form/Type/VichFileType.php#L141

   https://github.com/dustin10/VichUploaderBundle/blob/42131d64c29430666a811953e2ebe6411cf86ed1/src/Form/Type/VichFileType.php#L154
