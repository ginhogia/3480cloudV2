(function(w)
{
	w.fbAsyncInit = function()
	{
 		FB.init({
			appId      : window._r.fbAppId, // App ID
			channelUrl : "//" + location.host + "/channel.html", // Channel File
			status     : true, // check login status
			cookie     : true, // enable cookies to allow the server to access the session
			xfbml      : true // parse XFBML
		});

		FB.Event.subscribe('auth.authResponseChange', function(response)
		{
			console.log(response);
			if (response.status === 'connected')
			{

			}
			else if (response.status === 'not_authorized')
			{
			  location.pathname = "/";
			}
			else
			{
			  location.pathname = "/";
			}
		});
	};

	// Load the SDK Asynchronously
	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/all.js";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));

	$(document).ready(function()
	{
		$("[data-link='login']").click(function()
		{
			FB.login(function(response)
			{
				if (response.authResponse)
				{
					location.reload();
				}
				else
				{
					console.log('User cancelled login or did not fully authorize.');
				}
			});
		});
		$("[data-link='logout']").click(function()
		{
			FB.logout(function()
			{
				location.href = "/logout.php";
			});
		});

		var is_owner = _r.session.page.hasOwner(_r.session.user.fbid);
		if (!is_owner)
		{
			$("[data-visible='owner']").hide();
		}
	});
})(window);
