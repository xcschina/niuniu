<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2018101261631918",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDD9iIYR+YCRCuR
Jdff48Vbw7Ql2gtr4S+pT7SLOCsYOatiE8wrCHO1KJLrKwsB08UwvWQJdst2VSlr
M+tbopCrF/5D2lSIM5ZJSKXCCbEZFjcrUnu5QhYaDR0HircrOsvpf9962JqPO9B1
l6FKv1WU238wlEV8XF2dpHCi62Ru/rS97Ut1luQVD0GEUUSZ2QfOeUKsc6wZI3W8
5W7AOxM/LrjfSwr62xrpRCTf1CdRtsh8Uig+RCqWlE1YIasQSanSovS3WHQ1Jrpr
7Q74j7GykNqWh9uoVXF7iZOHcJOnSOAFVoJyKPlfYpAbSAwbPwMeRAZi2wl2zcdT
cMDLihFTAgMBAAECggEAeEIlnFcLTZq+Td42g9zJMk6A0LXmSncwg6w5dTKsJ7rE
dXUG1+Xg9G0I9K8+mwl2OzoPGUvBA0ayG2sKZfr24zjfPo7PT6Kw+RpPNywxdd7P
TpPY/9ERtT0U8prrW+lCWHK0GDSzbXFctb2vKysOf0LQ5GQp3G7muKbAxQrGYOJz
ir4mhhjdAdMLkst86F4UBQk3o55frP7hgSG2LOi01ShHgmYjR15AUQNyIsMgPDmN
gE4soXrIoIXdFrL03/l3KnXsiplldpAYprrQd8neSoPizg69DrnCxJMJuaAWRDqb
hiNoFHTP8n7Nb1ziST7Pda1+kSrwXOSssAa3bVyecQKBgQD/axz5A6WVlUl6ioPj
rhbZy2fKczQ2V6vGRnJn4LsbGbomdhAWc9RBtiU/t3igHfs5JOfWHTvNlYYU2oUP
+GoOxepyhL7fShYw1LVW9Zs3enhYGHElidYEWhWyZqKIAOoTkqH6BiVJ6mNCx25Q
qcxxo97MrwE4f3gspuuwC+G/uwKBgQDEaFybuBjJ/CqXo6FltVyttxyD5fqZk5I8
1SYOt6K8x4fNm+fPLGv8f8cX4vaGo8RbujRbg3KvpBOgjQpjZ610KLoRaqU+BX04
lsGjO1q9SuqW6YxQLaRAfV9/DLe3nXqfTTpGq7lP+K/+IzziZ0aBFBAsNeggzIdM
skPTe8xfSQKBgBOqdaKeq0MnKW8r9xeyscO3K5ik4iJFAc7UYb6pyP4/LtwDPx3b
cT5V/ew3/iul40/1DXyo54/esWItqQ0fvVrB1llW6zNaCvdbiVLWukq9PULbLusK
/9V58i24RU9fcqZrJdmQW5KjX10m6dGAIWOmkGMGHnvxJJmbiI4XfBw9AoGBAL4K
DLFPbAzkRYH7/cqftVEQgDLPb67xrunVg8FxxbDLj4dOdvlqjgH+0PE31jlodDIc
9VQm1+1C4QrT7V1Jj/d5ALChc8mTHhqHJE2AvMezmVD0IaPixa7woFoaQBkV8vP4
kY6X0fKjOga4qcyXCQ0UEKH2duhlffSj+CTFAHahAoGABHSTAxfVxuK6MVlhdpdF
787f8EciUiHFRHgk9aUsYmlB2pGRfkXEyYdxW7VQE0QDp+XU9WOTkEgzjjuB1cCb
jM3ALyNCR18ge4JLdGzQE1/ywKa6R5k/uAlM67lMh1AP+p41KSqKQFepQ7rfjn2q
v8cIwt1KkJhKCjnbrYs+Ws4=",
		
		//异步通知地址
		'notify_url' => "http://test.cc/alipay.trade.wap.pay-PHP-UTF-8/notify_url.php",
		
		//同步跳转
		'return_url' => "http://website.93kk.com/new_sdk",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkDsgTntJtqw9Fkj1OCf5d8LCyiXie5KUzqdl0f7tLtS9cstpI5I790kZJ+cB5IMkIuZnSRsLwaiGcSZWxzCuv4ngLxA2xZYTp/Q703iyTuXQVuE514E9YZoaiBfGvgkkHEwiszZHmZooWBVp/SzkImzGiSuPGJKqDMPBjrKFPmOaRIGlvdXThyJX8K2qL3Iquvi8tIn009nOOExmCtkAGpd5i71W4tWCseFULR7KCD8NPm0Qwoe50QKo8r+3axSZ8gDgyJOteIk8BC8XoxBgyhaGCUcRy5siMIitMiy73JlxOsXak2h2OQHieDTrwbr+5//Iq9BmujjN2WrSdL0+lQIDAQAB",
		
	
);