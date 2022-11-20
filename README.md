<p align="center">
<img src="https://static.codemao.cn/i/22/11/20/22/1838-SY.png" alt="iirose" width="100">
</p>

<h1 align="center">MoonLander 🌙</h1>

> 来自 [InkerBot](https://github.com/InkerBot) 的 [Ry-Sk/IIROSE-BOT](https://github.com/Ry-Sk/IIROSE-BOT)

<p align="center">这是为蔷薇花园里 `Saiki` 的机器人 `Moonface` 设计的登陆仓哦~</p>

<p align="center">为了能让她成功登陆到 `蔷薇星球`  真是费尽心思呢！</p>

<p align="center">🙇‍ 这里十分感谢 [<img src="https://i.loli.net/2020/05/11/bRMo78CNJP4HIiX.png" alt="iirose" width="18"> 蔷薇花园](https://iirose/com) 站长 `Ruby` 提供这么好的平台！</p>



## ⭐ 如何拥有同款登陆仓

### 🐧 使用 `Debian` / `Ubuntu`

> 这里举例 `Debian` 系，系统但是并不代表 `MoonLander` 只可以在这里搭建哦~

以下搭建环境均建立在使用非 `root` 用户，且拥有 `sudo` 权限

#### ❓ 如果你是 `Ubuntu`

```bash 
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
```

#### ❓ 如果你是 `Debian`
```bash 
sudo apt install apt-transport-https lsb-release ca-certificates -y
sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://mirror-cdn.xtom.com/sury/php/apt.gpg
sudo sh -c 'echo "deb https://mirror-cdn.xtom.com/sury/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
sudo apt update
```

#### 💻 环境安装
```bash
sudo apt install mediainfo php8.1-cli php8.1-dev php8.1-mysql php8.1-sqlite3 php8.1-curl php8.1-json php8.1-fileinfo php8.1-bcmath php8.1-xml
```

#### 💻 编译 / 安装 `Swoole` 扩展
```bash
git clone https://github.com/swoole/swoole-src.git
cd swoole-src
phpize
./configure --enable-openssl --enable-sockets --enable-mysqlnd
make
sudo make install
sudo echo 'extension=swoole.so' > /etc/php/8.1/mods-available/swoole.ini
sudo ln -s /etc/php/8.1/mods-available/swoole.ini /etc/php/8.1/cli/conf.d/20-swoole.ini
```

#### 💻 安装 `Composer` 

```bash
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer 
```

### ⚓ 使用 `Docker`

```bash
Todo...
```

### 🌙 MoonLander

```bash
git clone https://github.com/5t-RawBeRry/MoonLander
cd MoonLander
./install.sh
./iirosebot run
```

> 访问测试 [http://localhost:8008](http://localhost:8008)


## ❓ 一些其他问题的交代

> 暂时还没有前端，不过都在路上的，现在使用可以查看 [POSTMAN文档 ](https://documenter.getpostman.com/view/10410469/T1DiFzz8?version=latest)
