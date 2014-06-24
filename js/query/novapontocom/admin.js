// ===================================================
// confirmacao de acao 
// ===================================================

function confirmNovaPontoComAction(message, url)
{
	if(confirm(message))
	{
		window.location.href = url;
	}
}


// ===================================================
// janela de associacao de produtos
// ===================================================

function openNovaPontoComAssociateProductWindow(productId, url, title)
{
	Dialog.info
	(
		{
			url 				: url,
			options				: 
			{
				method: 'POST',
				parameters:
				{
					'product': productId
				}
			}
		},
		{
			id					: 'window_novapontocom_associate_prod' + productId,
			closable			: true,
			resizable			: true,
			draggable			: true,
			className 			: 'magento',
			windowClassName		: 'popup-window',
			title 				: title,
			top 				: 50,
			width 				: 940,
			height 				: 800,
			zIndex 				: 400,
			recenterAuto 		: false,
			hideEffect 			: Element.hide,
			showEffect 			: Element.show
		}
	);
}



// ===================================================
// janela de teste de configuracao 
// ===================================================

function openNovaPontoComConfigTestWindow(windowId, url, title)
{
	Dialog.info
	(
		{
			url 				: url
		},
		{
			id					: 'window_' + windowId,
			closable			: true,
			resizable			: true,
			draggable			: true,
			className 			: 'magento',
			windowClassName		: 'popup-window',
			title 				: title,
			top 				: 50,
			width 				: 400,
			height 				: 200,
			zIndex 				: 400,
			recenterAuto 		: false,
			hideEffect 			: Element.hide,
			showEffect 			: Element.show
		}
	);
}

function loadNovaPontoComConfigTestForm(url)
{
	var params  = new Array();
	params['configId'] = $('novapontocom_customer_config_window_configId').value;
	params['customerId'] = $('novapontocom_customer_config_window_customerId').value;

	// valida valor do usuario
	if(params['customerId'] == "")
	{
		$('novapontocom_customer_config_window_customerId').addClassName('validation-failed');
		return false;
	}
	else
	{
		$('novapontocom_customer_config_window_customerId').removeClassName('validation-failed');
	}

	// monta parametros
	switch(params['configId'])
	{
		case 'novapontocom_customer_individual_verify':
			
			params['name'] = $('novapontocom_customer_individual_name').value;
			params['last_name'] = $('novapontocom_customer_individual_last_name').value;
			params['cpf'] = $('novapontocom_customer_individual_cpf').value;
			params['rg'] = $('novapontocom_customer_individual_rg').value;
			params['cel_phone'] = $('novapontocom_customer_individual_cel_phone').value;
			break;
		
		case 'novapontocom_customer_company_verify':

			params['company_name'] = $('novapontocom_customer_company_company_name').value;
			params['ie'] = $('novapontocom_customer_company_ie').value;
			params['cnpj'] = $('novapontocom_customer_company_cnpj').value;
			break;

		case 'novapontocom_customer_address_verify':
			
			params['street'] = $('novapontocom_customer_address_street').value;
			params['number'] = $('novapontocom_customer_address_number').value;
			params['complement'] = $('novapontocom_customer_address_complement').value;
			params['district'] = $('novapontocom_customer_address_district').value;
			break;
	}

	new Ajax.Request(url,
	{
		method: 'POST',
		parameters: params,
		//loaderArea : 'novapontocom_customer_config_window_content',
		onComplete: function(response)
		{
			$('novapontocom_customer_config_window_content').update(response.responseText);
		}
	});
}

// ===================================================
// marcar envio como  'Delivered' na novaPontoCom 
// ===================================================
function postDeliveredShipment(url)
{
	window.location.href = url;
}