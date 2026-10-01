# Support uploadable fields in Doctrine ORM embeddables

Fixes #866, following the discussion in #350.

Uploadable fields can live inside Doctrine ORM embeddables, including embeddables nested in other
embeddables. They are registered on the entity under their dotted property path:

```twig
{{ vich_uploader_asset(promotion, 'images.banners.largeRectangle') }}
```

![vich_uploader_asset](https://github.com/user-attachments/assets/7e1fdaea-10c7-4be9-a9a1-fe3e5f3fd110)

## How it works

- `AttributeDriver` follows `#[ORM\Embedded]` properties (type from `class:` or the property type) and
  registers their uploadable fields with every mapped property prefixed by the embedded path:
  `images.thumbnail` with `fileNameProperty: images.thumbnailName`, `size: images.thumbnailSize`, ...
- `VichFileType` and `VichImageType` walk up from the field to the nearest non-embeddable form data and
  use that object with the dotted field name for the URI, download link and delete checkbox. Form types
  need no changes; nest the embeddable's form type as usual.
- `PropertyMapping` treats a `null` embeddable on the path as an empty field instead of throwing.

Only the entity needs `#[Vich\Uploadable]`. The `Vich\UploaderBundle\Entity\File` embeddable works inside
an embeddable too. Docs: `docs/embeddables.md`.

## Usage rule

Doctrine only flushes when a mapped column changes, so the embeddable needs its own `updatedAt` (or
any mapped column) set when a file is assigned, the same rule as for plain entities (#297).

## Tested

- Unit tests for the driver (nested, `class:` and typed `#[ORM\Embedded]`, prefixed properties,
  unresolvable types), the form type (root entity resolution, delete, embeddable as root form data) and
  `PropertyMapping` (null embeddable).
- [Demo project](https://github.com/jennevdmeer/vich-uploader-embeddable-test) on Symfony 7.4 and 8.1:
  create, replace, delete, entity removal, `Entity\File` metadata (name, size, mime type, original
  name, dimensions), profiler mapping panel, `vich:mapping:debug-class` and `vich:cleanup`.

![formtype](https://github.com/user-attachments/assets/245d28c2-abe4-484b-9ca7-6736c88e04e2)
![profiler](https://github.com/user-attachments/assets/9b08ca95-af19-429f-9bfe-4de1f4ee9e55)

## Out of scope

- YAML and XML mapping: those drivers do not follow embeddables.
- MongoDB ODM `EmbedOne`.
- Embeddables are detected in forms by their `#[ORM\Embeddable]` attribute, so XML-mapped embeddables
  are not walked.

## Questions

1. Is the dotted path acceptable as the public field name for `vich_uploader_asset`, the mapping
   collector and the commands?
2. Should YAML/XML support block this PR or follow separately?
3. Detecting embeddables in the form types by attribute (cached per class) versus asking Doctrine
   metadata, which would also cover XML-mapped embeddables but needs the manager registry in the form
   type: which do you prefer?
