# Doctrine embeddables in VichUploaderBundle: completion plan

Local work towards [dustin10/VichUploaderBundle#1509](https://github.com/dustin10/VichUploaderBundle/pull/1509).
Nothing is pushed to the PR until step 8.

## Setup

| | |
|---|---|
| Bundle | `D:/Webserver/VichUploaderBundle` |
| PR branch | `feature-vich-orm-embedded` (one commit on `upstream/master`, local only) |
| Work branch | `feature-vich-orm-embedded-wip`, branched from the PR branch |
| Old history | `backup/feature-vich-orm-embedded` |
| Test project | `D:/webserver/vich-uploader-embeddable-test` |

Bundle `master` is `3.x-dev`: PHP `^8.3`, Symfony `^6.4 || ^7.4 || ^8.0`, attributes only
(`Vich\UploaderBundle\Mapping\Attribute`).

## 1. Point the test project at the local bundle

- [x] Replace the `vcs` repository in `composer.json` with a `path` repository to
  `D:/Webserver/VichUploaderBundle` (symlinked).
- [x] Require `vich/uploader-bundle: 3.x-dev`.
- [x] Bump Symfony from `7.2.*` to `7.4.*` (later `8.0.*`, see step 6).
- [x] `composer update`.
- [x] Switch `Vich\UploaderBundle\Mapping\Annotation` to `Vich\UploaderBundle\Mapping\Attribute` in
  `Promotion`, `Images` and `Banners`.
- [x] `lint:container`, `doctrine:schema:validate` and `vich:mapping:debug-class "App\Entity\Promotion"`
  pass; the three dotted fields are listed.

## 2. Verify behaviour in the browser

Covers `Promotion` > `Images` > `Banners` (two levels of nesting).

- [x] Create a promotion with a thumbnail and both banners; files land in the right mapping directories.
- [x] Edit: replace one banner; the old file is removed, the name column is updated.
- [x] Edit: delete checkbox on each field removes the file and clears the name.
- [x] Download link and image preview in the default form theme resolve to the right URIs.
- [x] `vich_uploader_asset(promotion, 'images.thumbnail')` and
  `vich_uploader_asset(promotion, 'images.banners.largeRectangle')` render the right URIs.
- [x] Remove the promotion (Delete button on the edit page); all files are removed.
- [x] Profiler mapping collector lists the dotted fields.
- [x] `bin/console vich:mapping:debug-class "App\Entity\Promotion"` lists the dotted fields.

## 3. Settle the open questions

- [x] **`#[Vich\Uploadable]` on embeddables.** Not needed: only the root entity carries it. Create,
  replace and delete work with it removed from `Images` and `Banners`.
- [x] **Triggering the listener.** An upload inside an embeddable needs a mapped column in that
  embeddable to change (`Banners::$modifiedAt`, `Images::$updatedAt`). Without it the upload is
  dropped, the same rule as for plain entities.
- [x] **Private properties.** `Banners::$largeRectangle` (private, getter/setter) resolves through the
  accessors.
- [x] **Nullable embeddable.** Crashed in `PropertyMapping` (`UnexpectedTypeException` traversing
  `null`). Fixed on the WIP branch: reads return `null`, writes are skipped.
- [x] **`Vich\UploaderBundle\Entity\File` as embeddable** inside an embeddable: works for
  `Images::$attachment` once all mapped properties get the embedded path prefix (fixed on the WIP
  branch; only `fileNameProperty` was prefixed).
- [x] **Cleanup command.** `vich:cleanup --dry-run --min-age=0` counts the dotted-field references and
  lists a planted orphan.

## 4. Fill the gaps in the bundle

On `feature-vich-orm-embedded-wip`.

- [x] Fix whatever steps 2 and 3 turn up.
- [x] `isEmbeddable()` in `VichFileType`: cached per class. Attribute versus Doctrine metadata is a
  question for the maintainers in `PR.md`.
- [ ] YAML/XML drivers: listed as out of scope in `docs/embeddables.md` and `PR.md`; revisit if the
  maintainers want it in this PR.
- [x] Tests:
  - [x] `AttributeDriverTest`: builtin-typed property without `class` and non-existent class throw
    `DoctrineEmbeddedTypeNotFound`.
  - [x] Form type tests: root entity resolution, delete, embeddable as root form data (fixed a crash
    there).
  - [ ] Functional test with an embeddable fixture in `tests/Fixtures/TestBundle`: not added; ask the
    maintainers whether they want one on top of the unit tests.
- [x] `docs/embeddables.md`, linked from `docs/index.md`.

## 5. Quality checks

- [x] `vendor/bin/phpunit`: run with `php -d extension=pdo_sqlite -d extension=sqlite3`. All pass
  except the three `FileSystemStorageTest::uploadedFileIsCorrectlyMoved` cases, which fail on Windows
  path separators on `master` as well.
- [x] PHPStan as configured in the bundle's CI: no errors.
- [ ] Run php-cs-fixer / twig-cs-fixer yourself as the bundle's CI does.

## 6. Symfony 8

- [x] Switch the test project to Symfony 8 and repeat step 2. Runs on `8.1.*` (maker-bundle needs
  `^8.1`), DBAL 4, DoctrineBundle 3; create, replace, delete, entity removal and
  `vich_uploader_asset` pass.

## 7. PR description

- [x] Draft in `PR.md`, title as its heading. Review before posting.

## 8. Publish

- [ ] Squash or tidy `feature-vich-orm-embedded-wip` onto `feature-vich-orm-embedded`.
- [ ] `git push --force-with-lease origin feature-vich-orm-embedded`.
- [ ] Update PR title and body; mark ready for review if steps 2-6 pass.
- [ ] Delete `backup/feature-vich-orm-embedded` once the PR is green.
