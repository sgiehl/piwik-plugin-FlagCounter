# Piwik Flag Counter Plugin

## Description

This plugin allows you to include a small image or iframe in you website displaying the flags and total hits of the countries your website visitors came from

Please keep in mind that everyone will be able to see that kind of statistic as this plugin does not consider the access rights.

### Requirements

[Piwik](https://github.com/piwik/piwik) 2.0.4 or higher is required.

### Features

- Configurable with following parameters:
  - idSite: Website to display
  - period: period to display
  - date: date to display
  - cols: count of columns to display (default 2)
  - rows: count of rows to display (default 5)
  - showflag: show flags (default 1)
  - showcountryode: show country codes (default 0)
- Generates a transparent PNG image showing the flag icons and their total hits
- Generates simple HTML to be included as iframe

## FAQ

__The image is not displayed. What can I do?__

Maybe you have some kind of access restriction for your Piwik like http auth. The Url needs to be public accessible, or at least accessible to those, who should be able to see the counter.

__How can I display the counter for all data in the past__

To do that, set period to ```range``` and date to something like ```1992-01-01,today```.

The full URL for the image would then look like:
```
http://piwik.url/index.php?module=FlagCounter&action=image&idSite=1&period=range&date=1992-01-01,today&cols=2&rows=6
``` 

## Changelog

- Version 0.1 - Alpha Release
- Version 0.2 
  - improved image generation (automatic spacing between items)
  - possibility to show country codes besides or instead of the flags

## Support

Please direct any feedback to [stefan@piwik.org](mailto:stefan@piwik.org)

## Contribute

Feel free to create issues and pull requests.

## License

GPLv3 or later

