<header>
</header>
<script>
	
	$(document).ready(function(){

		$("#logout-link").on("click",function(e){
			e.preventDefault();
			document.getElementById('logout-form').submit();
		})

	});
</script>