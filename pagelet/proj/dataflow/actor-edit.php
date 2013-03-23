<?php
$projbase = H5C_DIR;

$proj  = preg_replace("/\/+/", "/", rtrim($this->req->proj, '/'));
if (substr($proj, 0, 1) == '/') {
    $projpath = $proj;
} else {
    $projpath = "{$projbase}/{$proj}";
}

$obj = $projpath ."/dataflow";
$obj = preg_replace(array("/\.+/", "/\/+/"), array(".", "/"), $obj);
if (!is_writable($obj)) {
    die("'$obj' is not Writable");
}

list($grpid, $actorid) = explode("/", $this->req->uri);

$fsg = $obj."/{$grpid}.grp.json";
if (!file_exists($fsg)) {
    die("Bad Request");
}
$grp = file_get_contents($fsg);
$grp = json_decode($grp, true);
if (!isset($grp['id'])) {
    die("Internal Server Error");
}

$fsj = $obj."/{$grpid}/{$actorid}.actor.json";
if (!file_exists($fsj)) {
    die("Bad Request");
}
$actor = file_get_contents($fsj);
$actor = json_decode($actor, true);
if (!isset($actor['id'])) {
    die("Internal Server Error");
}
$exec_cron = array("*","*","*","*","*");
if (isset($actor['exec_cron'])) {
    $exec_cron = explode(",", $actor['exec_cron']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($this->req->name);
    if (!strlen($name)) {
        die('`name` can not be null');
    }
    $actor['exec_mode']     = intval($this->req->exec_mode);
    $actor['exec_timeout']  = intval($this->req->exec_timeout);
    $actor['exec_interval'] = intval($this->req->exec_interval);
    $actor['exec_cron']     = implode(",", $this->req->exec_cron);
    $actor['name']          = $name;
    $actor['updated']       = time();
    file_put_contents($fsj, hwl_Json::prettyPrint($actor));

    die("OK");
}
?>
<style>
.exec_regular td {
    width: 80px;
}
.exec_regular input {
    width: 30px;
}
</style>
<form id="sy9p3x" action="/h5creator/proj/dataflow/actor-edit" style="padding:5px;">
  <input type="hidden" name="proj" value="<?php echo $this->req->proj?>" />
  <input type="hidden" name="uri" value="<?php echo $this->req->uri?>" />
  <table width="100%" cellpadding="20px" style="border-spacing:20px;">
    
    <tr>
      <td width="160px">Group</td>
      <td>
        <?php echo $grp['name']?>
      </td>
    </tr>
    
    <tr>
      <td>Name your Actor</td>
      <td><input type="text" name="name" value="<?php echo $actor['name']?>" /></td>
    </tr>
    
    <tr>
      <td>Execution Mode</td>
      <td>
        <select name="exec_mode" onchange="_exec_mode(this.value)" style="width:400px">
        <?php
        $vs = h5creator_service::listExecMode();
        foreach ($vs as $k => $v) {
          $select = '';
          if ($k == $actor['exec_mode']) {
            $select = 'selected';
          }
          echo "<option value='{$k}' {$select}>{$v}</option>";
        }
        ?>
        </select>
        <div id="nyj2w2">
        </div>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <div class="exec_timeout qvzhnl hide">
          <label>Timeout <font color="red">*</font></label>
          <div class="input-append">
            <input class="input-small" type="text" name="exec_timeout" value="<?php echo $actor['exec_timeout']?>" />
            <span class="add-on">Second</span>
          </div>
        </div>
        
        <div class="exec_interval qvzhnl hide">
          <label>Time interval <font color="red">*</font></label>
          <div class="input-append">
            <input class="input-small" type="text" name="exec_interval" value="<?php echo $actor['exec_interval']?>" />
            <span class="add-on">Second</span>
          </div>
        </div>
        
        <div class="exec_regular qvzhnl hide" >
        <label>Regular time <font color="red">*</font> (same to unix crontab setting)</label>
        <pre> Example of time definition:
 .---------------- minute (0 - 59)
 |  .------------- hour (0 - 23)
 |  |  .---------- day of month (1 - 31)
 |  |  |  .------- month (1 - 12)
 |  |  |  |  .---- day of week (0 - 6) (Sunday=0)
 |  |  |  |  |
 *  *  *  *  *</pre>
        <table>
          <tr>
            <td>minute</td>
            <td>hour</td>
            <td>day</td>
            <td>month</td>
            <td>week</td>
          </tr>
          <tr>
          <?php
          for ($i = 0; $i < 5; $i++) {
            echo "<td><input type='text' name='exec_cron[{$i}]'' value='{$exec_cron[$i]}'/></td>";
          }
          ?>
          </tr>
        </table>

      </td>
    </tr>
    
    <tr>
      <td>Parallel Mode</td>
      <td>
        <select name="para_mode" onchange="_para_mode(this.value)">
        <?php
        $vs = h5creator_service::listParaMode();
        foreach ($vs as $k => $v) {
          $select = '';
          if ($k == $actor['para_mode']) {
            $select = 'selected';
          }
          echo "<option value='{$k}' {$select}>{$v}</option>";
        }
        ?>
        </select>
      </td>
    </tr>

    <tr>
      <td></td>
      <td><input type="submit" class="btn btn-primary" value="Save" /></td>
    </tr>
  </table>
  
</form>


<script type="text/javascript">

function _exec_mode(val)
{
    $(".qvzhnl").hide();

    if (val == 1 || val == 2 || val == 3) {
        $(".exec_timeout").show();
    }
    
    if (val == 2) {
        $(".exec_regular").show();
    }

    if (val == 3) {
        $(".exec_interval").show();
    }
}
_exec_mode(<?php echo $actor['exec_mode']?>);

$("#sy9p3x").submit(function(event) {

    event.preventDefault();
    
    $.ajax({
        type    : "POST",
        url     : $(this).attr('action'),
        data    : $(this).serialize(),
        timeout : 3000,
        success : function(rsp) {
            
            if (rsp == "OK") {
                hdev_header_alert('success', rsp);
            } else {
                hdev_header_alert("error", rsp);
            }
        },
        error: function(xhr, textStatus, error) {
            hdev_header_alert('error:', textStatus +' '+ xhr.responseText);
        }
    });
});
</script>