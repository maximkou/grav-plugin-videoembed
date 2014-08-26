Youtube plugin for [Grav CMS](http://getgrav.com)
-------------------------------------------------

This plugin convert Youtube links in page content to embed format.

## Example

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
Simply add plugin dependency into `.dependencies` file, e.g:
```
git:
    youtube:
        url: https://github.com/maximkou/grav-plugin-youtube.git
        path: user/plugins/youtube
        branch: master

links:
    youtube:
        src: grav-plugin-youtube
        path: user/plugins/youtube
        scm: github
```

And then run `php bin/grav install`. That's all!

## Configuration

All configuration rules located in `youtube.yaml`

### Default config

```
enabled: true

# default embed container html attributes
container_html_attr:
    class: video-container

# default embed element html attributes
embed_html_attr:
    frameborder: 0
    width      : 560
    height     : 315

# default embed video params
embed_options:
    autoplay   : 1
    autohide   : 1
    fs         : 1
    rel        : 0
    hd         : 1
    vq         : hd1080
    wmode      : opaque
    enablejsapi: 1

```

You can override default `embed_options` for each video, for do this simply add params to end of video url.
Example (using default options): link `http://youtu.be/AsdjHDHksdf?autoplay=0&wmode=transparent` will be converted to:

```html
<div class="video-container">
	<iframe src="//youtube.com/embed/AsdjHDHksdf?autoplay=0&autohide=1&fs=1&rel=0&hd=1&vq=hd1080&wmode=transparent&enablejsapi=1"></iframe>
</div>
```

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