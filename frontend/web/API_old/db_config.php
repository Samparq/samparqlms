$('#saveBtn').click(function(){
          $.ajax({
            url: "http://192.168.4.201/clientrole/frontend/web/client/client-branch",
            dataType: "json",
            data: {
                zid:zoneId
            },
            success:function(data){
				if(data.status == 200){
					console.log("done");
				}
			},
            error:function(){
                console.log("something went wrong please try again later");
            }
        });
});