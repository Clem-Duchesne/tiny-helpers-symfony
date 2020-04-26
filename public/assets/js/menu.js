var button = document.getElementsByClassName('menu');
var sidebar = document.getElementsByClassName('sidebar');
var body = document.body;

function openMenu(){
    


        sidebar[0].classList.toggle('hide');
        sidebar[0].classList.toggle('show');
    
    
    
    
    button[0].children[0].classList.toggle('la-bars');
    button[0].children[0].classList.toggle('la-times');
    body.classList.toggle('overflow');
}
button[0].addEventListener('click', openMenu, false);

