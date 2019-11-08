# PHPVulFiner

PHP代码静态自动化审计工具


# 现状

刚开始写没多久

目前正在写AST生成四元组，想将数据流更明显地暴露出来。

基本块划分还未添加，以及后续的数据流分析等步骤同样暂未添加。

由于对跳转语句的四元组生成的有些情况缺少考虑，目前的四元组生成看起来比较混乱，orz

# 启动

进入到项目主目录，运行

> php -S 0.0.0.0:9095

通过浏览器访问`http://0.0.0.0:9095`即可访问

# 参考

- [https://github.com/OneSourceCat/phpvulhunter](https://github.com/OneSourceCat/phpvulhunter)