var sessionId = document.getElementById('session').value;
var select = document.getElementById('tool_user');

var selectChildren = select.children;
var n = selectChildren.length;

for(let i=0; i<n;i++){
    if(selectChildren[i].value == sessionId){
        selectChildren[i].style.display = 'none';
    }
}
console.log(selectChildren);