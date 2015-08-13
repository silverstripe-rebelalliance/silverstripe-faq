# FAQ

## Maintainers

 * Wilfred Godfrey (wjagodfrey)
 * Denise Rivera (d-rivera-c)
 * Ben Manu (benmanu)

## Introduction

This module provides FAQ functionality on top of Solr for CWP 1.1.*.

## Requirements

 * CWP 1.1.0
 * CWP-core branch master, commit 3e26cd3fba5ee0588121351e71513c50260fa751
 * Fulltextsearch module, branch master, commit f2f16ae863a7281f7db1c17c0bd29cb09c18aa68

When the versions for cwp-core and silverstripe-fulltextsearch get tagged, the requirements for this module will change
to that stable versions.

## Installation

### Composer

Edit your project-wide composer.json file as follows; in the "require" block add:

    "silverstripe/faq": "*"

Then in the root of your project run:

    #> composer update silverstripe/faq

### Web

To begin the installation first download the module online. Download the module as a zip file from the github page.

After you have finished downloading the file, extract the downloaded file to your site's root
folder and ensure the name of the module is `faq`.

### All

Run a database rebuild by visiting *http://yoursite.com/dev/build*. This will add the required database
columns and tables for the module to function.

## What does it do

Just by adding the module to one project, you'll get a ModelAdmin for FAQs, where you can manage Questions & Answers.
You only need to add an FAQPage type (comes with the module), and some questions and answers.

The module comes with its own Solr search index, customized to have a fuzzy search, and has its own file for stopwords and synonyms.

## Quickstart

1. Add a FAQPage to your CMS
![](docs/images/faq-pagetype.png)

2. Add an FAQ on the FAQ ModelAdmin sidebar link
![](docs/images/faq-modeladmin.png)

3. Wait for Solr to reindex
4. Search your FAQ page :)
![](docs/images/faq-frontend.png)

## Configuration

### Frontend templates

Can be overloaded within the project themes folder. Default templates can be found on the `faq/templates`.

### Configuration using YAML

FAQ module comes with a default set of templates to start the Solr index. If you want to override the path from where this files
come from, you need to add a YAML file like this.
```
---
Name: faqoverride
After: 'faq/*'
---
FAQSearchIndex:
  options:
    extraspath: 'new/path/to/extrapath'
    templatespath: 'new/path/to/template'
```

## TODO

- Get static config variables from yml files
- Pagetypes, search index and dataobjects easily extendable
