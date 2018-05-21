@include('partials.header')
    <h1>ads.txt Validator</h1>
            
    <p class="notice rcorners15">
        <strong>Notice: </strong> Please only enter the primary domain. The application 
        will add "/ads.txt" the domain automatically.
    </p>
    
    @include('partials.messages')
                
    @include('partials.validate-url-form')            
            
@include('partials.footer')
