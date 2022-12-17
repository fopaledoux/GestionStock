<?php
    if(isset($_GET['ids']))
    {
        $conn->query("UPDATE `sales_list` SET `status`= 1 WHERE id='{$_GET['ids']}'");
    }
?>
<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">Rapports de ventes</h3>
        <div class="card-tools">
        <form action="" method="post">
            <input type="date" name="datefirst" id="" >
            <input type="date" name="dateSecond" id="" >
            <button type="submit" class="btn btn-flat btn-primary">Rechercher</button>
            </form>
            
		</div>
	</div>
	<div class="card-body" id="print_out">
		<div class="container-fluid">
        <div class="container-fluid">
			<table class="table table-bordered table-stripped">
                    <colgroup>
                        <col width="5%">
                        <col width="15%">
                        <col width="20%">
                        <col width="20%">
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date de creation</th>
                            <th>Code Vente</th>
                            <th>Client</th>
                            <th>Nombre d'Articles</th>
                            <th>Montant total</th>
                            <th>Statut</th>
                            <th>Vendeur</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(isset($_POST['datefirst']) && $_POST['dateSecond'] ):?>
                   
                        <h3 class="text-primary"> Rapport du <?=$_POST['datefirst']?> au <?=$_POST['dateSecond']?>  <button class="btn btn-flat btn-success" type="button" id="print">Imprimer</button> </h3> 
                        <?php 
                        $i = 1;
                        if(  $_POST['datefirst'] === $_POST['dateSecond'] )
                        {
                           //echo 'yes';
                            $qry = $conn->query("SELECT *  FROM `sales_list`  where date(`date_created`)=DATE( '".$_POST['datefirst']."')");
                        
                        }else{
                        $qry = $conn->query("SELECT *  FROM `sales_list`  where `date_created` BETWEEN '".$_POST['datefirst']."' and '".$_POST['dateSecond'] ."' order by `date_created` desc");
                        }
                        $som=0;
                        while($row = $qry->fetch_assoc()):
                            $row['items'] = count(explode(',',$row['stock_ids']));
                            $som+=$row['amount'] ;
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td><?php echo date("Y-m-d H:i",strtotime($row['date_created'])) ?></td>
                                <td><?php echo $row['sales_code'] ?></td>
                                <td><?php echo $row['client'] ?></td>
                                <td class="text-right"><?php echo number_format($row['items']) ?></td>
                                <td class="text-right"><?php echo number_format($row['amount'],2) ?></td>
                                
                                <td class="text-center">
                                    <?php if($row['statuts'] == 0): ?>
                                        <span class="badge badge-primary rounded-pill">Non livré</span>
                                    <?php elseif($row['statuts'] == 1): ?>
                                        <span class="badge badge-success rounded-pill">Livré</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger rounded-pill">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['vendeur'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <?php endif?>
                </table>
                <h4 class="text-primary"> TOTAL: <?php echo  isset($som)? $som : '0'?> FCFA</h4>
		</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		$('.delete_data').click(function(){
			_conf("Voulez supprimer l\'article definitvement ?","delete_sale",[$(this).attr('data-id')])
		})
		$('.table td,.table th').addClass('py-1 px-2 align-middle')
		$('.table').dataTable();
	})
	function delete_sale($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_sale",
			method:"POST",
			data:{id: $id},
			dataType:"json",
			error:err=>{
				console.log(err)
				alert_toast("Une erreur s\'est produite.",'error');
				end_loader();
			},
			success:function(resp){
				if(typeof resp== 'object' && resp.status == 'success'){
					location.reload();
				}else{
					alert_toast("Une erreur s\'est produite.",'error');
					end_loader();
				}
			}
		})
	}
    $(function(){
        $('#print').click(function(){
            start_loader()
            var _el = $('<div>')
            var _head = $('head').clone()
                _head.find('title').text("RAPPORT DE VENTE- Apercu avant impression")
            var p = $('#print_out').clone()
            p.find('tr.text-light').removeClass("text-light bg-navy")
            _el.append(_head)
            _el.append('<div class="d-flex justify-content-center">'+
                      '<div class="col-1 text-right">'+
                      '<img src="<?php echo validate_image($_settings->info('logo')) ?>" width="65px" height="65px" />'+
                      '</div>'+
                      '<div class="col-10">'+
                      '<h4 class="text-center"><?php echo $_settings->info('name') ?></h4>'+
                      '<h4 class="text-center">RAPPORT DE VENTE</h4>'+
                      '</div>'+
                      '<div class="col-1 text-right">'+
                      '</div>'+
                      '</div><hr/>')
            _el.append(p.html())
            var nw = window.open("","","width=1200,height=900,left=250,location=no,titlebar=yes")
                     nw.document.write(_el.html())
                     nw.document.close()
                     setTimeout(() => {
                         nw.print()
                         setTimeout(() => {
                            nw.close()
                            end_loader()
                         }, 200);
                     }, 500);
        })
    })
</script>