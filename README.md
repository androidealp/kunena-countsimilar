# kunena-countsimilar
Generate a button on the Kunena forum at the post, which counts the number of users with similar problems to post.

### Version 
![status](https://api.travis-ci.org/androidealp/kunena-countsimilar.svg?branch=master)

kunena-countsimilar 1.1 Stable 

### Download
[Direct download](https://github.com/androidealp/kunena-countsimilar/blob/master/countsimilar.zip?raw=true)

### Configuration

You must modify the template layout in com_kunena/template/custom_template/html/topic/default_actions.php

After that insert or update

```php
echo $this->topicButtons->get('mesmoproblema').' '.$this->topicButtons->get('countmesmoproblema');
```

### Attention:

The plugin needs jquery to run properly, it is not inserted to prevent incompatibility problems in the system, if you have not jquery running on the frontend the button will not function.

### Tabela

The plugin uses its own table for managing tales #__kunena_sameproblem.

### Examples

* Insert code in com_kunena/template/custom_template/html/topic/default_actions.php

![Example 1](https://github.com/androidealp/kunena
kunena-countsimilar/blob/master/prints/exemple1.jpg "Example 1")

* login to user forum and open post

![Example 2](https://github.com/androidealp/kunena
kunena-countsimilar/blob/master/prints/exemple2.jpg "Example 2")

* Effect after click

![Example 3](https://github.com/androidealp/kunena
kunena-countsimilar/blob/master/prints/exemple3.jpg "Example 3")

### Tests

Tests were run in Joomla 3.4.x and Kunena versão 4.0.7 / 4.0.9


