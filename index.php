<?php
 //Include the Router logic
 include_once './phpLogicPack/router.php';

 Router::get("/", function ($allParams, $pathParams, $requestData) {
    echo "All params (including query): ";
    print_r($allParams);
    echo "Path params: ";
    print_r($pathParams);
    echo "Request data (GET): ";
    print_r($requestData);
});

// Router::post("/users", function ($allParams, $pathParams, $requestData) {
//     echo "All params (including query): ";
//     print_r($allParams);
//     echo "Path params: ";
//     print_r($pathParams);
//     echo "Request data (POST): ";
//     print_r($requestData);
// });

    Router::get("/users/{id}", function ($fullParams, $query, $queryData) {
        echo "All params (including query): ";
        print_r($fullParams);
        echo "Path params: ";
        print_r($query);
        echo "Request data (GET): ";
        print_r($queryData);
    });

    Router::get("/user/{id}", function ($allParams, $pathParams, $requestData) {
        echo "All params (including query): ";
        print_r($allParams);
        echo "Path params: ";
        print_r($pathParams);
        echo "Request data (GET): ";
        print_r($requestData);
    });

Router::handleRequest();



 ?>


    <!-- <script>


            function Index(){
                return (

                    `  
                        <h1>Index</h1>
                        ${Buttons()}
                    `
                )
            }

            function Dashboard(){
                return (

                    `  
                        <h1>Dashboard</h1>
                        ${Buttons()}
                    `
                )
            }
            function About(){
                return (

                    `  
                        <h1>About</h1>
                        ${Buttons()}
                    `
                )
            }


            function PageNotFound(){
                return (

                    `  
                        <h1>PageNotFound</h1>
                        ${Buttons()}
                    `
                )
            }
            function Buttons(){
                return (

                    `  
                        <button>index</button>
                        <button>dashboard</button>
                        <button>about</button>
                    `
                )
            }




            function Router(routes, invalidRoute){
                let path = window.location.pathname;
                let query = window.location.search;

                let payload;
                let url;
                let route = Object.keys(routes)

                

                if (!query){
                       url = path.split('/')
                        payload = url[url.length - 1].toLowerCase() 
                        displaydata(payload)

                        // console.log(payload);
                        
                 
                }else if (query){
                    url = path.split('=')[1].toLowerCase().trim()
                    payload = url
                    // displaydata(payload)
                }else {
                    displaydata()
                }



                function displaydata(data){
                    if(!route.includes(data)){
                        document.body.innerHTML = invalidRoute()
                    }else{
                        const page = route[data]
                        document.body.innerHTML = page()
                    }

                    addbtnlisteners()
                }
            }



            function addbtnlisteners() {
                let buttons = document.querySelectorAll("button")

                buttons.forEach((btn) => {
                    let button = btn.innerText.toLowerCase()


                    window.history.pushState(null, button, `${button}`)

                    Router({'/': Index, Dashboard, About}, PageNotFound)
                })
            }



            Router({'/': Index, Dashboard, About}, PageNotFound)
    </script> -->
</body>
</html>
