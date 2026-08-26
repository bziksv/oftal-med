<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$buttonId = $this->randString();
?>
<div class="bx-subscribe"  id="sender-subscribe">

	<?if(isset($arResult['MESSAGE'])): CJSCore::Init(array("popup"));?>
		<div id="sender-subscribe-response-cont" style="display: none;">
			<div class="bx_subscribe_response_container">
				<table>
					<tr>
						<td style="padding-right: 40px; padding-bottom: 0px;"><img src="<?=($this->GetFolder().'/images/'.($arResult['MESSAGE']['TYPE']=='ERROR' ? 'icon-alert.png' : 'icon-ok.png'))?>" alt=""></td>
						<td>
							<div style="font-size: 22px;"><?=GetMessage('subscr_form_response_'.$arResult['MESSAGE']['TYPE'])?></div>
							<div style="font-size: 16px;"><?=htmlspecialcharsbx($arResult['MESSAGE']['TEXT'])?></div>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<script>
			BX.ready(function(){
				var oPopup = BX.PopupWindowManager.create('sender_subscribe_component', window.body, {
					autoHide: true,
					offsetTop: 1,
					offsetLeft: 0,
					lightShadow: true,
					closeIcon: true,
					closeByEsc: true,
					overlay: {
						backgroundColor: 'rgba(57,60,67,0.82)', opacity: '80'
					}
				});
				oPopup.setContent(BX('sender-subscribe-response-cont'));
				oPopup.show();
			});
		</script>
	<?endif;?>

	<form action="<?=$arResult["FORM_ACTION"]?>" class="subscribe__form" method="post" id="bx_subscribe_subform_<?=$buttonId?>" role="form">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="sender_subscription" value="add">

		<input class="subscribe__input" type="email" name="SENDER_SUBSCRIBE_EMAIL" value="<?=$arResult["EMAIL"]?>" title="<?=GetMessage("subscr_form_email_title")?>" placeholder="<?=htmlspecialcharsbx(GetMessage('subscr_form_email_title'))?>">
		<button class="subscribe__btn" id="bx_subscribe_btn_<?=$buttonId?>"><span><?=GetMessage("subscr_form_button")?></span></button>

		<div id="subscribe-consent-wrap">
			<label class="subscribe__label">
				<input type="checkbox" class="subscribe__checkbox" id="subscribe-consent" name="subscribe-consent">
				<i class="icon-checkbox"></i>
				<?php include $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/include/legal_form_consent.php'; ?>
			</label>
			<div class="mf-consent-error" style="display:none;color:#ffc107;margin-top:8px;">
				Необходимо дать согласие на обработку персональных данных
			</div>
		</div>


	</form>

	<script>
		BX.ready(function () {
			var btn = BX('bx_subscribe_btn_<?=$buttonId?>');
			var form = BX('bx_subscribe_subform_<?=$buttonId?>');

			if (!btn || !form) {
				return;
			}

			function mailSender()
			{
				setTimeout(function() {
					if (!btn) {
						return;
					}

					var btnSpan = btn.querySelector('span');
					var btnSubscribeWidth = btnSpan.style.width;
					BX.addClass(btn, 'send');
					btnSpan.outerHTML = "<span><i class='fa fa-check'></i> <?=GetMessage("subscr_form_button_sent")?></span>";
					if (btnSubscribeWidth) {
						btn.querySelector('span').style['min-width'] = btnSubscribeWidth + 'px';
					}
				}, 400);
			}

			function showConsentError(show)
			{
				var wrap = BX('subscribe-consent-wrap');
				var errorEl = wrap ? wrap.querySelector('.mf-consent-error') : null;

				if (errorEl) {
					errorEl.style.display = show ? 'block' : 'none';
				}

				if (show && typeof alertify !== 'undefined') {
					alertify.error('Необходимо дать согласие на обработку персональных данных');
				}
			}

			function isConsentChecked()
			{
				var consent = form.querySelector('#subscribe-consent');
				return consent && consent.checked;
			}

			BX.bind(form, 'submit', function (e) {
				if (!isConsentChecked()) {
					showConsentError(true);
					e.preventDefault();
					return false;
				}

				showConsentError(false);
				setTimeout(mailSender, 250);
				btn.disabled = true;
				setTimeout(function () {
					btn.disabled = false;
				}, 2000);

				return true;
			});

			var consent = form.querySelector('#subscribe-consent');
			if (consent) {
				BX.bind(consent, 'change', function () {
					if (isConsentChecked()) {
						showConsentError(false);
					}
				});
			}
		});
	</script>

</div>