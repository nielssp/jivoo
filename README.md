# Jivoo – PHP web application framework

[![Build Status](https://travis-ci.org/jivoo/jivoo.svg?branch=master)](https://travis-ci.org/jivoo/jivoo) [![Coverage Status](https://coveralls.io/repos/jivoo/jivoo/badge.svg?branch=master&service=github)](https://coveralls.io/github/jivoo/jivoo?branch=master)

Jivoo is an experimental web application framework for PHP.

Although the framework is usable at the moment (currently used on [agendl.com](http://agendl.com), [nielssp.dk](http://nielssp.dk), and [apakoh.dk](http://apakoh.dk)), I do not recommend using it for anything important.

Some examples of simple applications are available in the examples directory.

## Goals

1. Portable: Applications should work on most web servers running at least PHP 5.3 using one of the supported database systems (MySQL, SQLite and PostgreSQL for the time being).
2. Fast prototyping: Built-in development tools for generating and configuring applications.
3. Robust: Support for database migrations, installation/updating, and error handling.

## Features

* Routing system
* Model–View–Controller
* Active records
* Database abstraction
* Database migration
* Template system
* Installation and update system
* Extension system
* Command line interface

## Module overview

### AccessControl

Classes for authentication and authorization.

### ActiveModels

ActiveRecord/ActiveModel system.

### Assets

Asset system for static images, scripts, stylesheets, etc.

### Console

Development tools including a toolbar.

### Content

Content abstraction, e.g. formats and editors.

### Control

Supposed to combine and replace Controllers, Snippets, and Helpers.

### Controllers

MVC controller base class.

### Core

Core framework classes. Includes caching, I18n, CLI, logging, configuration, etc.

### Data

Data abstraction. Supposed to replace Models and parts of Databases.

### Databases

Database abstraction. Includes drivers for MySQL, PostgreSQL, and SQLite.

### Extensions

Enables application extensions and third-party libraries.

### Helpers

Application helpers.

### Jtk

A GUI toolkit for applications.

### Migrations

Migration system for databases.

### Models

Data abstraction and query system.

### Routing

Routing system and request/response abstraction.

### Setup

Interactive GUI-based installation/update system.

### Snippets

An alternative to controllers.

### Themes

A theme system.

### Vendor

Supposed to replace Extensions and Themes. Support for installing/updating third-party packages, and better integration with composer and possible other package managers.

### View

Presentation layer. Includes two template systems.
