## 最简单的使用

创建类：

```
<?php

namespace app\Model;

use tourze\Model\Model;

class Article extends Model
{

    protected $_tableName = 'article';

}
```

### 查找单条记录

```
// 通过主键来查找
$article = new Article(1);
echo $article->id;
```

或

```
// 通过查询条件来查找
$article = new Article([
    'status' => 0,
    'id' => 1,
]);
echo $article
```

也可以这样使用

```
$article = new Article;
$article->where('id', '=', 1);
$article->find();
if ($article->loaded())
{
    echo $article->id;
}
```

[!!] 直接传参到构造方法，模型会自动从数据库中加载记录。否则就需要手动调用`find()`方法。另外，要判断记录是否被加载，可以使用`loaded()`方法来判断。

### 查找多条记录

```
$articles = (new Article)
    ->where('category_id', '=', 0)
    ->findAll();
/** @var Article $article */
foreach ($articles as $article)
{
    echo $article->title . "<br />";
}
```

[!!] `findAll()`方法返回一个数组，记录了符合条件的所有记录

### 创建记录

```
$article = new Article;
$article->title = 'Test Title';
$article->content = '<p>TEST</p>';

// 也可以直接使用values方法，从外部数组加载数据
// $article->values($_POST);

try
{
    $article->save();
    echo $article->id;
}
catch (\tourze\Model\Exception\ValidationException $e)
{
    print_r($e->errors);
}
```

[!!] 创建记录时，如果字段规则校验出错的话，会抛出一个\tourze\Model\Exception\ValidationException异常，其中包含了出错信息。

### 更新记录

更新记录跟创建记录操作类似，区别在于更新记录，首先需要有一个已经存在的记录

```
$article = new Article(1);
if ( ! $article->loaded())
{
    exit('Article not found');
}

try
{
    $article->title = 'Test Title';
    $article->content = '<p>TEST</p>';

    // 也可以直接使用values方法，从外部数组加载数据
    // $article->values($_POST);

    $article->save();
    echo $article->id;
}
catch (\tourze\Model\Exception\ValidationException $e)
{
    print_r($e->errors);
}
```

### 删除记录

删除记录，同样需要先加载对应的记录

```
$article = new Article(1);
if ( ! $article->loaded())
{
    exit('Article not found');
}

try
{
    $article->delete();
}
catch (\tourze\Model\Exception\ValidationException $e)
{
    print_r($e->errors);
}
```

### 自动过滤

有时候，我们在设置模型数据时，希望能对数据进行自动过滤或转换。例如对于上面的Article模型，我们需要做到：

* title过滤两侧空格
* content过滤空格
* content过滤危险字符

如果按照普通的做法，我们可能需要在设置数据时，手工过滤一次。这种做法不利于项目的稳定维护，调用次数多了，就容易出现差错。
所以，我们可以为模型增加一个`filters()`方法，使其能自动过滤数据。

更改模型代码为：

```
<?php

namespace app\Model;

use tourze\Model\Model;

class Article extends Model
{

    protected $_tableName = 'article';

    /**
     * @inheritdoc
     */
    public function filters()
    {
        return [
            'title' => [
                ['trim']
            ],
            'content' => [
                ['trim']
                ['safeTrim', ':value', 'script']
            ]
        ];
    }

    public function safeTrim($content, $script)
    {
        return str_replace($script, '', $content);
    }

}
```

规则参考上面的例子来写即可。模型会在设置对应字段的值时触发。

模型在过滤数据时，对于传入的函数，会按以下顺序来查找：

1. 顶级命名空间的函数
2. 当前模型中的方法
3. tourze\Base\Security\Valid中的静态方法

### 数据校验

在上面的CURD例子中，我们使用了try{...}catch(){...}来捕捉异常。那么同样地，我们需要在模型中定义好字段的校验规则。
校验规则的书写例子跟filters中类似，可以对照着来写。
规则定义好后，在调用`save()`、`create()`、`update()`方法时会自动校验。

```
<?php

namespace app\Model;

use tourze\Model\Model;

class Article extends Model
{

    protected $_tableName = 'article';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'title' => [
                ['notEmpty']
            ],
            'content' => [
                ['notEmpty']
            ]
        ];
    }

}
```
