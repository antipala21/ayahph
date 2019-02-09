<?php
echo $this->Html->script('jquery-1.11.2.min');
echo $this->Html->script('requester');
?>
<style>
#container {
    width: 100%;
}
</style>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="container" style="margin-top: 100px;">
    <div class="alert alert-success">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>
                    <span class="glyphicon glyphicon glyphicon-share-alt"></span> Json Requester
                </h2>
            </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="url">URL</label>
                        <input type="text" name="url" id="url" class="form-control" placeholder="Ente your url here"/>
                    </div>
                    <div class="form-group">
                        <label for="url">JSON</label>
                        <textarea class="form-control" rows="5" id="value"></textarea>
                    </div>
                </div>
                <div class="panel-footer">
                    <input type="submit" id="submit" value="Send" class="btn btn-primary"/>
                </div>
        </div>
        <div class="breadcrumb">
            <legend>Result</legend>
            <prev>
            <div id="result"></div>
            </prev>
        </div>
    </div>
</div>