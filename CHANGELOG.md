# Changelog

All notable changes to `laravel-ray` will be documented in this file

## 1.13.0 -2021-02-22

- add exception watcher

## 1.12.6 - 2021-02-10

- replace spaces with underscores in `env()` calls (#154)

## 1.12.5 - 2021-02-10

- fix "Package spatie/laravel-ray is not installed" exception (#156)

## 1.12.4 - 2021-02-10

- handle edge case where ray proxy would not be set

## 1.12.3 - 2021-02-08

- chain colours on `show*` methods (#149)

## 1.12.2 - 2021-02-07

- ignore errors caused by using `storage_path`

## 1.12.1 - 2021-02-05

- register watchers on boot (#138)

## 1.12.0 - 2021-02-03

- remove enabled methods (#132)

## 1.11.2 - 2021-02-02

- do not blow up when using `Mail::fake()`

## 1.11.1 - 2021-02-01

- update config file

## 1.11.0 - 2021-01-31

- add view requests
- add view cache

## 1.10.1 - 2021-01-31

- display logged exceptions

## 1.10.0 - 2021-01-29

- add view methods

## 1.9.3 - 2021-01-28

- internals cleanup

## 1.9.2 - 2021-01-28

- improve dependencies

## 1.9.1 - 2021-01-25

- improve service provider

## 1.9.0 - 2021-01-22

- add `showJobs`

## 1.8.0 - 2021-01-19

- the package will now select the best payload type when passing something to `ray()`

## 1.7.1 - 2021-01-17

- lower dependencies

## 1.7.0 - 2021-01-15

- make `model` more flexible

## 1.6.1 - 2021-01-15

- better support for logged mailables

## 1.6.0 - 2021-01-15

- add `markdown` method

## 1.5.2 - 2021-01-13

- fix headers on response payload

## 1.5.1 - 2021-01-13

- make the test response macro chainable

## 1.5.0 - 2021-01-13

- add `testResponse` method

## 1.4.0 - 2021-01-13

- let the `model` call accepts multiple models.

## 1.3.6 - 2021-01-13

- update `str_replace()` calls in `ray:publish-config` with `env()` usage (#82)

## 1.3.5 - 2021-01-12

- improve recognizing mails in logs

## 1.3.4 - 2021-01-09

- add `env()` vars for each Laravel config setting (#55)

## 1.3.3 - 2021-01-09

- add `enabled()` and `disabled()` methods (#54)

## 1.3.2 - 2021-01-09

- fix frame for `rd` function

## 1.3.1 - 2021-01-09

- fix broken `queries()`-method (#51)

## 1.3.0 - 2021-01-08

- Add `PublishConfigCommand`

## 1.2.0 - 2021-01-08

- add support for `local_path` and `remote_path` settings

## 1.1.0 - 2021-01-07

- add support for Lumen (#22)

## 1.0.3 - 20201-01-07

- fix incompatibilities on Windows (#20)
- fix host settings (#14)

## 1.0.2 - 2021-01-07

- fix deps

## 1.0.1 - 2021-01-07

- fix deps

## 1.0.0 - 2021-01-07

- initial release
