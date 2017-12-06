# Getting Started With Sample Dashboard

![Dashboard Sample Page](https://github.com/qiscus/dashboard-sample/blob/master/img/1512532707-list+user.png)

If you have your own chat app, you may be wondering how you can manage your users. In this case, we provide a sample dashboard for user management. This sample dashboard can help you to figure out how to work with Qiscus Server Api for more advanced functionalities. You can go to https://www.qiscus.com/documentation/rest/list-api to know more about Server API.


> Note: We assume that you have already downloaded the sample app. The sample app will be needed to work together with the sample dashboard.


You can explore the [sample dashboard](http://dashboard-sample.herokuapp.com/login) to try it online, or you also can download the source code to deploy it locally or to your own server.

To start trying the sample dashboard on your end, you should carry out the following steps:
Clone sample dashboard in our [github](https://github.com/qiscus/dashboard-sample), or just copy the following code.
```
git clone https://github.com/qiscus/dashboard-sample.git
cd dashboard-sample
```
Before running the sample app on your local, you need to first install composer. 
```
composer install
php -S localhost:8000
```

The sample dashboard provided Client API to enable your sample app get list of users. This API is based on PHP and used Composer as its dependency manager. Thus, you need to have PHP and composer installed to use the API.


Now you would have successfully run the sample dashboard. However, do note that the sample app is running using our App ID. If you want the sample dashboard to be connected to your app with your own App ID, you need to change the App ID and Secret Key in the sample dashboard login page. You can find your own App ID and Secret Key in your own [Qiscus SDK dashboard](https://www.qiscus.com/dashboard).

If you are wondering how our sample app with dashboard works, here are some illustrations:

![Image of Dashboard Sample](https://github.com/qiscus/dashboard-sample/blob/master/1511248325-How%2Bsample%2Bwork.png)

There are 2 Server API that are used inside Qiscus Sample Dashboard:

1. ```.qiscus.com/api/v2.1/rest/get_user_list``` to get list of users from Qiscus SDK database, and
2. ```.qiscus.com/api/v2/rest/login_or_register``` to enable user login or register via Sample Dashboard.

The Sample Dashboard called these APIs inside main.js file. To use these APIs, you need to pass your APP ID and  set method and request parameter.

To pass the APP ID, If you login to Sample Dashboard with your own APP ID and Secret Key, the APP ID and Secret Key has been saved, so that you need nothing to setup APP ID inside main.js.  

To set method and request parameter, you can refer to [Qiscus Server API Documentation](https://www.qiscus.com/documentation/rest/list-api) on “Get User List” and “Login and Register” section.

The Sample Dashboard also provided API for client app to get list of users from the Sample Dasboard. 
To enable your client app to get list of users, you need to set your APP ID and Secret Key inside .env file. Then, you need to pass your domain name when calling the API.
```
//your-domain.com/api/contacts
Example: //dashboard-sample.herokuapp.com/api/contacts
```

You will get the response as follow:
```
{
   "results":{
      "meta":{
         "total_data":123,
         "total_page":6
      },
      "users":[
         {
            "avatar_url":"https:\/\/d1edrlpyc25xu0.cloudfront.net\/kiwari-prod\/image\/upload\/75r6s_jOHa\/1507541871-avatar-mine.png",
            "created_at":"2017-12-05T08:07:58.405896Z",
            "email":"tesweqeq",
            "id":452773,
            "name":"tesweqeq",
            "updated_at":"2017-12-05T08:07:58.405896Z",
            "username":"tesweqeq"
         }
      ]
   },
   "status":200
}
```
