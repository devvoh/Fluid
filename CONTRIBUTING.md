# So you want to contribute to Parable?

Great! Though it is important to remember what the design goals for Parable are:

1. It needs to be fast.
2. It needs to be small.
3. It needs to be readable and easy to extend.
4. It doesn't need to do everything.

Anything that doesn't adhere to these goals, might not be accepted. Know that up front. If you want to make sure before creating a Pull Request, you can create an issue to describe the change you'd like to suggest first. 

## How to Pull Request

It is strongly requested to create a separate PR _per change_. This makes it easier to merge specific issues and an issue with one change in a PR won't block the other change.

1. Fork the Parable repository.
2. Create a new branch per feature or improvement.
3. Send a pull request from each of your branches against the version branch for which your fix is intended.

## Style Guide

All pull requests must adhere to the [PSR-2 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md). If there's any part that doesn't do so, StyleCI _will_ complain. 

## Unit Testing

All pull requests must be accompanied by passing tests and complete code coverage. Use `make coverage` to check. Parable uses phpunit for testing.