Embed Video plugin for [Grav CMS](http://getgrav.org)
-------------------------------------------------
[![Build Status](https://travis-ci.org/maximkou/grav-plugin-videoembed.svg?branch=v1.0)](https://travis-ci.org/maximkou/grav-plugin-videoembed)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/maximkou/grav-plugin-videoembed/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/maximkou/grav-plugin-videoembed/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/maximkou/grav-plugin-videoembed/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/maximkou/grav-plugin-videoembed/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/127bf39f-d49a-4c9b-965d-2eb97e384fe4/mini.png)](https://insight.sensiolabs.com/projects/127bf39f-d49a-4c9b-965d-2eb97e384fe4)

This plugin convert links to videos from popular sharing services to embed format. Supported services:

* Youtube
* Vimeo
* Coub.com
* ... you can propose more services [here](https://github.com/maximkou/grav-plugin-videoembed/issues)

## Working example

This `.md` page source:
```
Home
------------
## See this youtube video!

https://www.youtube.com/watch?v=rZudJiJcw3s

```
Will be converted to:
```html
<h1>Home</h1>
<h2>See this youtube video!</h2>
<div>
	<iframe src="//youtube.com/embed/rZudJiJcw3s"></iframe>
</div>
```


## Installation
There are two ways to install plugin:

1. Download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `videoembed`. 
You should now have all the plugin files under `/your/site/grav/user/plugins/videoembed`

2. Simply add plugin dependency into `.dependencies` file, e.g:
```
git:
    videoembed:
        url: https://github.com/maximkou/grav-plugin-videoembed.git
        path: user/plugins/videoembed
        branch: master
```
And then run `php bin/grav install`

## Configuration

All configuration rules located in `videoembed.yaml`

### Default config

```
enabled: true

# responsive video size
responsive: false

# embed element container, if this section empty - not use container
container:
    element: div
    # container element html attributes
    html_attr:
        class: video-container

# supported services configs
services:
    youtube:
        # you can disable support 
        enabled: true
        # embed element html attributes, for youtube element is iframe
        embed_html_attr:
            frameborder: 0
            width      : 560
            height     : 315
        # options, which you will add for videos
        embed_options:
            autoplay   : 1
            autohide   : 1
            fs         : 1
            rel        : 0
            hd         : 1
            vq         : hd1080
            wmode      : opaque
            enablejsapi: 1
     vimeo:
        # some config
     ....

```

### Customizing single video/page videos parameters
If you need set custom plugin parameters for single page, set plugin parameters in page header in section `videoembed`, e.g:

```
---
# ... more headers
videoembed:
    services:
        youtube:
            embed_options:
                fs: 2
---
```

You can override default `embed_options` for each video, for do this simply add params to end of video url. Your params will be applied on default embed options.

Example (using default options), link 
```
http://youtu.be/AsdjHDHksdf?autoplay=0&wmode=transparent
```
will be converted to something like this:

```html
<div class="video-container">
	<iframe src="//youtube.com/embed/AsdjHDHksdf?autoplay=0&rel=0&hd=1&vq=hd1080&wmode=transparent"></iframe>
</div>
```

### Responsive video size
If you want to have responsive video size, set `responsive` option to `true`.
This option add responsiveness support(in 16:9 ratio) by using [this method for iframes](http://css-tricks.com/NetMag/FluidWidthVideo/Article-FluidWidthVideo.php).
If this option enabled, plugin add `plugin-videoembed-container-fluid` class for container.

**Attention:** this option requires defined `container.element` option. If option is not defined - you got plugin error. Disable responsiveness, if you want not use video wrapper.

## License
The MIT License (MIT)

Copyright (c) 2014 Maxim Hodyrev

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
