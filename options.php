<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Beeralex\Core\Config\Fields\Checkbox;
use Beeralex\Core\Config\Fields\Input;
use Beeralex\Core\Config\Fields\Select;
use Beeralex\Core\Config\Tab;
use Beeralex\Core\Config\TabsBuilder;
use Beeralex\Gigachat\Options;
use Beeralex\Gigachat\Services\ModelsService;

$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);
$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);

if ($POST_RIGHT < "S") {
    $APPLICATION->AuthForm('Недостаточные права доступа');
}
Loader::includeModule($module_id);

$models = [
    '' => 'Выберите модель' 
];
$defaultModel = '';
try {
    $modelsCollection = (new ModelsService())->getModels();
    foreach($modelsCollection->models as $model){
        $models[$model->id] = $model->id;
    }
    $defaultModel = $modelsCollection->first()->id;
    if(!Options::getInstance()->defaultModel){
        Option::set($module_id, 'gigachat_model', $defaultModel);
    }
    
}catch (\Throwable $e){}

$mainTab = new Tab('edit1', 'Общие настройки', 'Интеграция с API GigaChat');
$mainTab->addField((new Input('authorization_key', 'Ключ авторизации'))->setLabel('Обязательные'));
$mainTab->addField((new Input('scope', 'Scope')));
$mainTab->addField((new Input('base_oauth_url', 'Базовый адрес запроса для получения токена'))->setDefaultValue('https://ngw.devices.sberbank.ru:9443'));
$mainTab->addField((new Input('base_gigachat_url', 'Базовый адрес запроса к GigaChat API'))->setDefaultValue('https://gigachat.devices.sberbank.ru'));
$mainTab->addField((new Select('gigachat_model', 'Модель формирующая ответ на сообщение', $models))->setDefaultValue($defaultModel)->setLabel('Прочие'));
$mainTab->addField((new Checkbox('logs_enable', 'Включить логирование')));
$mainTab->addField((new Checkbox('cert_enable', 'Сертификат НУЦ Минцифры установлен на уровне системы')));
$accessTab = new Tab("edit2", Loc::getMessage("MAIN_TAB_RIGHTS"), Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"));
$tabsBuilder = (new TabsBuilder())->addTab($mainTab)->addTab($accessTab);

$tabs = $tabsBuilder->getTabs();

if ($request->isPost() && check_bitrix_sessid()) {
    foreach ($tabs as $tab) {
        $fileds = $tab->getFields();
        if (!isset($fileds)) {
            continue;
        }
        foreach ($fileds as $filed) {
            if($name = $filed->getName()){
                if ($request["apply"]) {
                    $optionValue = $request->getPost($name);
                    $optionValue = is_array($optionValue) ? implode(",", $optionValue) : $optionValue;
                    Option::set($module_id, $name, $optionValue);
                }
                if ($request["default"]) {
                    Option::set($module_id, $name, $filed->getDefaultValue());
                }
            }
        }
    }
}
// отрисовываем форму, для этого создаем новый экземпляр класса CAdminTabControl, куда и передаём массив с настройками
$tabControl = new CAdminTabControl(
    "tabControl",
    $tabsBuilder->getTabsFormattedArray()
);

// отображаем заголовки закладок
$tabControl->Begin();
?>

<form action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= $module_id ?>&lang=<?= LANG ?>" method="post">
    <? foreach ($tabs as $tab) {
        if ($options = $tab->getOptionsFormattedArray()) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $options);
        }
    }
    $tabControl->BeginNextTab();

    require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php";

    $tabControl->Buttons();
    echo (bitrix_sessid_post());
    ?>
    <input class="adm-btn-save" type="submit" name="apply" value="Применить" />
    <input type="submit" name="default" value="По умолчанию" />
</form>
<?
// обозначаем конец отрисовки формы
$tabControl->End();