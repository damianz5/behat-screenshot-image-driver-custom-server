Custom Server image driver for Behat-ScreenshotExtension
=========================
[![Build Status](https://travis-ci.org/damianz5/behat-screenshot-image-driver-custom-server.svg?branch=master)](https://travis-ci.org/damianz5/behat-screenshot-image-driver-custom-server)

This package is an image driver for the [bex/behat-screenshot](https://github.com/elvetemedve/behat-screenshot) behat extension which uploads to your custom server.

You can use [damianz5/simple_file_server](https://github.com/damianz5/simple_file_server) as simple file server.

Installation
------------

Install by adding to your `composer.json`:

```bash
composer require --dev damianz5/behat-screenshot-image-driver-custom-server
```

Configuration
-------------

Enable the image driver in the Behat-ScreenshotExtension's config in `behat.yml` like this:

```yml
default:
  extensions:
    Bex\Behat\ScreenshotExtension:
      active_image_drivers: custom_server
      image_drivers:
        custom_server:
          authkey: authorisation key # Required
          collection_name: name of the collection # Required
          request_url: http://server.ltd/api/upload/ # Required
          image_base_url: http://server.ltd # Required
```

Alternative configuration
-------------
Options can be passed by OS exports (for travis):

in console / using travis encrypted variables:
```bash
export BEHAT_SCREENSHOT_AUTHKEY="authorisation key"
export BEHAT_SCREENSHOT_COLLECTION_NAME="beefbeefbeefbeefbeefbeefbeefbeef"
export BEHAT_SCREENSHOT_REQUEST_URL="http://server.ltd/api/upload/"
export BEHAT_SCREENSHOT_IMAGE_BASE_URL="http://server.ltd"
```

behat.yml (no need to specify the configuration details):
```yml
default:
  extensions:
    Bex\Behat\ScreenshotExtension:
      active_image_drivers: custom_server
```

Third configuration method (without editing the behat.yml file)
-------------
```bash
export BEHAT_PARAMS='{"extensions" : {"Bex\\Behat\\ScreenshotExtension" : {"active_image_drivers" : "custom_server"}}}'
export BEHAT_SCREENSHOT_AUTHKEY="authorisation key"
export BEHAT_SCREENSHOT_COLLECTION_NAME="beefbeefbeefbeefbeefbeefbeefbeef"
export BEHAT_SCREENSHOT_REQUEST_URL="http://server.ltd/api/upload/"
export BEHAT_SCREENSHOT_IMAGE_BASE_URL="http://server.ltd"
```

Usage
-----

When you run behat and a step fails then the Behat-ScreenshotExtension will automatically take the screenshot and will pass it to the image driver, which will return the custom server image url. So you will see something like this:

```bash
  Scenario:                           # features/feature.feature:2
    Given I have a step               # FeatureContext::passingStep()
    When I have a failing step        # FeatureContext::failingStep()
      Error (Exception)
Screenshot has been taken. Open image at http://server.ltd/....
    Then I should have a skipped step # FeatureContext::skippedStep()
```
