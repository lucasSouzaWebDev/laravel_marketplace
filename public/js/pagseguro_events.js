let cardNumber = document.querySelector('input[name=card_number]');
let spanBrand  = document.querySelector('span.brand');

cardNumber.addEventListener('keyup', function(){
    if(cardNumber.value.length > 5){
        PagSeguroDirectPayment.getBrand({
            cardBin: cardNumber.value.substr(0, 6),
            success: function(res){
                let imgFlag = `<img src="https://stc.pagseguro.uol.com.br/public/img/payment-methods-flags/68x30/${res.brand.name}.png">`;
                spanBrand.innerHTML = imgFlag;
                document.querySelector('input[name=card_brand').value = res.brand.name;
                getInstallments(amountTransaction, res.brand.name);
            },
            error: function(error){
                console.log(error);
            }
        });
    }
});

let submitButton = document.querySelectorAll('button.proccessCheckout');

submitButton.forEach(function(el, k){
    el.addEventListener('click', function(event){
        event.preventDefault();
        document.querySelector('div.msg').innerHTML = '';
        let buttonTarget = event.target;
        buttonTarget.disabled = true;
        buttonTarget.textContent = 'Carregando...';
        let paymentType = event.target.dataset.paymentType;
        if(paymentType === 'CREDITCARD'){
            PagSeguroDirectPayment.createCardToken({
                cardNumber:      document.querySelector('input[name=card_number]').value,
                brand:           document.querySelector('input[name=card_brand]').value,
                cvv:             document.querySelector('input[name=card_cvv]').value,
                expirationMonth: document.querySelector('input[name=card_month]').value,
                expirationYear:  document.querySelector('input[name=card_year]').value,
                success: function(res){
                    proccessPayment(res.card.token, buttonTarget, paymentType);
                },
                error: function(error){
                    buttonTarget.disabled = false;
                    buttonTarget.textContent = 'Efetuar Pagamento';
                    for(let i in error.errors){
                        document.querySelector('div.msg').innerHTML = showErrorMessages(errorsMapPagseguroJS(i));
                    }
                    
                }
            });
        }

        if(paymentType === 'BOLETO'){
            proccessPayment(null, buttonTarget, paymentType);
        }
        
    });
});

