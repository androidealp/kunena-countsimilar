# kunema-countsimilar
Gera um botão no fórum do Kunena na parte de post, que contabiliza a quantidade de usuários com problemas similares ao post

### Versão 
kunema-countsimilar 1.0 estável 

### Configurações

Você pode baixar também através do link [www.next4.com.br/blog](http://www.next4.com.br/blog/) 

É necessário modificar o layout de template em com_kunena/template/custom_template/html/topic/default_actions.php

Após isso inserir ou estilizar

```php
echo $this->topicButtons->get('mesmoproblema').' '.$this->topicButtons->get('countmesmoproblema');
```

### Atenção:

O plugin precisa do jquery para rodar corretamente, não foi inserido para evitar problemas de incompatibilidade no sistema, caso não tenha o jquery rodando no frontend o botão não irá funcionar.

### Tabela

O plugin utiliza sua própria tabela para gerenciamento dos counts #__kunena_sameproblem.

### Testes

Foram feitos testes no Joomla 3.4.x e no Kunena versão 4.0.7
