

<div class="vbx-applet openvbx-zendesk-applet">
    <h2>This applet creates a ticket in ZenDesk for calls</h2>
    <p>Enter in the URL subdomain you use for Desk</p>
    <textarea class="small" name="subdomain"><?php 
        echo AppletInstance::getValue('subdomain') 
    ?></textarea>
    <p>Enter in the ZenDesk API Token</p>
    <textarea class="small" name="apitoken"><?php 
        echo AppletInstance::getValue('apitoken') 
    ?></textarea>
   <p>Enter in the email address</p>
    <textarea class="small" name="email"><?php 
        echo AppletInstance::getValue('email') 
    ?></textarea>

 <br />
    <h2> Select An Action for The Caller</h2>
    <?php echo AppletUI::DropZone('primary'); ?>
</div>

