## 💰 Please Support This Project!

[![](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=53CD2WNX3698E&lc=US&item_name=PREngineer&item_number=PayPal-IPN-Integrations&currency_code=USD&bn=PP%2dPayPal-IPN-Integrations%3abtn_donateCC_LG%2egif%3aNonHosted)

![Bitcoin Donation](https://raw.githubusercontent.com/PREngineer/AppImgs/main/btc.png)
19JXFGfRUV4NedS5tBGfJhkfRrN2EQtxVo

# PayPal IPN Integrations

This project contains implementations of PayPal IPN integrations using different languages.

# About

This is a standalone container that you can use to perform PayPal IPN transactions.

You can find it containerized [here](https://hub.docker.com/r/prengineer/paypal-ipn-integration).

# What does it do?

It receives a POST from your application with the information to initiate a PayPal transaction. It submits the transaction to PayPal. Then, it processes the IPN notification from PayPal. If the transaction is validated/successful, it forwards the information to your application for processing.

# Application scenarios

1. Your application submits the POST to this container. It will show the user a page letting them know that they are being transferred to PayPal to complete the checkout. The container waits the time that you specify. The container then submits the transaction details by redirecting the user.

2. The user cancels the PayPal checkout. PayPal will redirect them back to the container (You must provide the return URL for cancellations, like: https://payments.mystore.com/index.php?action=cancel⁠). They will see a notification that the transaction was cancelled and will be transferred to the Cart page of your application after a wait time that you determine.

3. The user completes the PayPal checkout. PayPal will redirect the user back to the container (You must provide the return URL for cancellations, like: https://payments.mystore.com/index.php?action=complete⁠). They will see a notification that the checkout was completed and will be transferred to the Orders page of your application after a wait time that you determine.

4. PayPal submits an Instant Payment Notification. Upon the completion of the checkout, PayPal will then send an IPN to a hidden endpoint (the user doesn't see it). (You must provide the return URL, like: https://payments.mystore.com/index.php?action=ipn⁠) The container will perform the validation (submitting the information back to PayPal for confirmation) and if the response from PayPal is that the transaction is "VERIFIED", then passes the IPN data to your application on a script that you specify in an ENV variable.

NOTE: You can implement your own Cancel and Complete pages if you want, instead of relying on the container's. Just change the URL that is configured in the container's ENV variables. You do need to POST to the root inside the container (https://payments.mystore.com/index.php⁠) and use the <<action=ipn>> endpoint though (https://payments.mystore.com/index.php?action=ipn⁠). This is assuming that you host this container in https://payments.mystore.com⁠.

# Application endpoints

| Base | URL Endpoint | Required | Purpose |
| -- | -- | --| -- |
|pay.site.com | /index.php | YES | Initiate the transaction|
|pay.site.com | /index.php?action=cancel | NO | Acknowledge transaction cancellation|
|pay.site.com | /index.php?action=complete | NO | Acknowledge completed checkout|
|pay.site.com | /index.php?action=ipn | YES | Perform IPN validation|