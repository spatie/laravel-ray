# Changelog

All notable changes to `laravel-ray` will be documented in this file

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
