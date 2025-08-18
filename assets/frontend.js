document.addEventListener('DOMContentLoaded', function(){
    const catSelect = document.getElementById('bpi_cat');
    const subSelect = document.getElementById('bpi_sub');
    if(catSelect && subSelect){
        catSelect.addEventListener('change', function(){
            const parent = this.value;
            subSelect.innerHTML = '<option value="">'+subSelect.getAttribute('data-placeholder')+'</option>';
            if(!parent){return;}
            fetch('/wp-json/wp/v2/bpi_category?parent='+parent)
                .then(res=>res.json())
                .then(data=>{
                    data.forEach(term=>{
                        const opt=document.createElement('option');
                        opt.value=term.id;
                        opt.textContent=term.name;
                        subSelect.appendChild(opt);
                    });
                });
        });
    }

    // modal handling
    const modal = document.getElementById('bpi-modal');
    const modalBody = modal ? modal.querySelector('.bpi-modal-body') : null;
    document.querySelectorAll('.bpi-result-card').forEach(card=>{
        card.addEventListener('click', function(){
            if(!modal || !modalBody){return;}
            modalBody.innerHTML = this.querySelector('.bpi-card-details').innerHTML;
            modal.classList.add('open');
        });
    });
    if(modal){
        modal.addEventListener('click', function(e){
            if(e.target.classList.contains('bpi-close') || e.target === modal){
                modal.classList.remove('open');
            }
        });
    }
});
