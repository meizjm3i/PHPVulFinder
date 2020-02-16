# PHPVulFinder

PHP代码静态自动化审计工具


# 现状


AST生成四元组目前仅完成了一部分，仍有许多情况未进行处理，在现有基础上开始写基本块划分和代码优化优化的逻辑。

数据流分析暂时未开始编写。

目前的四元组生成有部分处理逻辑存在错误、疏漏。

# 启动

> 本地环境：
> 
> php7.1.8
> 
> Mac OSX 10.14

进入到项目主目录，运行

> php -S 0.0.0.0:9095

通过浏览器访问`http://0.0.0.0:9095`即可访问

# 参考

- [https://github.com/OneSourceCat/phpvulhunter](https://github.com/OneSourceCat/phpvulhunter)