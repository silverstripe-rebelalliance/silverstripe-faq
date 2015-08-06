# FAQ

## Maintainers

 * Wilfred Godfrey (wjagodfrey)
 * Denise Rivera (d-rivera-c)
 * Ben Manu (benmanu)

## Introduction

This module provides FAQ functionality on top of Solr for CWP 1.1.*.

## Requirements

 * CWP 1.1.*

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
