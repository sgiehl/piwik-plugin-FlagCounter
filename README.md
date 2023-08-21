# Matomo Flag Counter Plugin

## Description

This plugin allows you to include a small image or iframe in your website displaying the flags and total hits of the countries your website visitors came from

Please keep in mind that everyone will be able to see that kind of statistic as this plugin does not consider the access rights.

### Requirements

[Matomo](https://github.com/matomo-org/matomo) 2.0.4 or higher is required.
GD library including ttf support.

### Features

- Configurable with following parameters:
  - idSite: Website to display
  - period: period to display
  - date: date to display
  - cols: count of columns to display (default 2)
  - rows: count of rows to display (default 5)
  - showflag: show flags (default 1)
  - showcountryode: show country codes (default 0)
  - font: font family to use
  - fontsize: font size to use (between 2 and 30; default 12)
  - fontcolor: font color to use (rgb value; default 0,0,0)
- Generates a transparent PNG image showing the flag icons and their total hits
- Generates simple HTML to be included as iframe

## FAQ

__The image is not displayed. What can I do?__

Maybe you have some kind of access restriction for your Matomo like http auth. The Url needs to be public accessible, or at least accessible to those, who should be able to see the counter.

__How can I display the counter for all data in the past__

To do that, set period to ```range``` and date to something like ```1992-01-01,today```.

The full URL for the image would then look like:
```
http://matomo.url/index.php?module=FlagCounter&action=image&idSite=1&period=range&date=1992-01-01,today&cols=2&rows=6
``` 

This URL can then be simply used in an img tag on your site. e.g.
```
<img src="http://matomo.url/index.php?module=FlagCounter&action=image&idSite=1&period=range&date=1992-01-01,today&cols=2&rows=6" />
```

__Can I use a custom font?__

Currently all ttf fonts located in the ```fonts``` directory within this plugin are available for usage. If you place a new font there, you should be able to use it.


## Changelog

- Version 0.1 - Alpha Release
- Version 0.2 
  - improved image generation (automatic spacing between items)
  - possibility to show country codes besides or instead of the flags
- Version 0.3
  - added ability to choose a font family, size & color
- Version 3.0.0
  - Compatibility for Piwik 3
- Version 3.0.3
  - fixed caching bug with multiple sites
- Version 4.0.0
  - Compatibility with Matomo 4
- Version 5.0.0
  - Compatibility with Matomo 5

## Support

Please direct any feedback to [stefan@matomo.org](mailto:stefan@matomo.org)

## Contribute

Feel free to create issues and pull requests.

## License

GPLv3 or later

Note: The fonts bundled with this plugin are under the following licenses
- OpenSans: Apache license
- Roboto: Apache license
- Raleway: SIL Open Font License
- Cantarell: SIL Open Font License

