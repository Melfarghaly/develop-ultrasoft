@extends('layouts.app')
@section('title', __('مراكز التكلفة'))

@section('content')
@include('cost_center.partials.nav')
<style>
  .tree, .tree ul {
    margin:0;
    padding:0;
    list-style:none
}
.tree ul {
    margin-right:1em;
    position:relative
}
.tree ul ul {
    margin-right:.5em
}
.tree ul:before {
    content:"";
    display:block;
    width:0;
    position:absolute;
    top:0;
    bottom:0;
    right:0;
    border-right:1px solid
}
.tree li {
    margin:0;
    padding:0 1em;
    line-height:2em;
    color:#369;
    font-weight:700;
    position:relative;
    cursor: pointer;
}
.tree ul li:before {
    content:"";
    display:block;
    width:10px;
    height:0;
    border-top:1px solid;
    margin-top:-1px;
    position:absolute;
    top:1em;
    right:0
}
.tree ul li:last-child:before {
    background:#fff;
    height:auto;
    top:1em;
    bottom:0
}
.indicator {
    margin-right:5px;
}
.tree li a {
    text-decoration: none;
    color:#369;
}
.tree li button, .tree li button:active, .tree li button:focus {
    text-decoration: none;
    color:#369;
    border:none;
    background:transparent;
    margin:0px 0px 0px 0px;
    padding:0px 0px 0px 0px;
    outline: 0;
}
.last-record{
   
    text-decoration: underline;
    font-style: italic;
}
</style>


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('مراكز التكلفة')</h1>
        <!-- <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol> -->
    </section>
<section class="content no-print">
@component('components.widget', ['class' => 'box-primary', 'title' => __('مركز التكلفة')])       
    <div class="container">
    <div class="row">
        <!-- قسم إنشاء مركز تكلفة جديد -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">إضافة مركز تكلفة جديد</div>
                <div class="card-body">
                    <form action="{{ route('cost-center.store') }}" method="POST">
                        @csrf
                        <div class="mb-3 " >
                            <label for="code" class="form-label">كود المركز</label>
                            <input type="text" class="form-control" id="code" name="code" >
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">اسم المركز</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">المركز الرئيسي</label>
                            <select class="form-control select2" id="parent_id" name="parent_id">
                                <option value="">لا يوجد</option>
                                @foreach($costCenters as $center)
                                    <option value="{{ $center->id }}"  data-code="{{$child->code ?? 0 }}" data-childs="{{$center->children->count()  ?? 0 }}">{{ $center->name }}</option>
                                    @if($center->children->count() > 0)
                                        @foreach($center->children as $child)
                                        @if(!$child->is_last_record)
                                            <option value="{{ $child->id }}" data-code="{{$child->code ?? 0 }}" data-childs="{{$center->children->count()  ?? 0 }}">-- {{ $child->name }}</option>
                                        @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                                                        
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_last_record" name="is_last_record">
                            <label class="form-check-label" for="is_last_record">آخر مستوى</label>
                        </div>
                        <button type="submit" class="btn btn-success mt-3">حفظ</button>
                        <a href="{{ route('cost-center.index') }}" class="btn btn-danger mt-3">إلغاء</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- قسم عرض شجرة مراكز التكلفة -->
        <div class="col-md-8">
    <div class="card">
        <div class="card-header bg-info text-white">شجرة مراكز التكلفة</div>
        <div class="card-body">
            <ul id="tree2">
                @foreach($costCenters as $center)
                    @if(!$center->parent_id)
                        <li>
                            
                            <span class="toggle-node cost-center-node {{ $center->is_last_record ? 'last-record' : '' }}"
                                data-id="{{ $center->id }}"
                                data-code="{{ $center->code }}"
                                data-name="{{ $center->name }}"
                                data-childs="{{ $center->children->count() }}"
                                data-parent-id="{{ $center->parent_id ?? '' }}"
                                data-is-last="{{ $center->is_last_record ? 'true' : 'false' }}">
                                <i class="icon"></i> {{ $center->code }} - {{ $center->name }}
                            </span>
                            <span class="badge bg-info rounded-pill pull-left hide">{{ $center->children->count() }}</span>
                            <!-- delete button -->
                            
                             <button type="button" class="btn btn-default btn-sm delete-node" style="color:red !important" data-id="{{ $center->id }}" data-name="{{ $center->name }}" data-toggle="modal" data-target="#deleteModal">
                                <i class="fa fa-trash"></i> 
                            </button>
                            <!-- delete button -->

                            @if($center->children->count() > 0)
                                <ul class="nested">
                                    @include('cost_center.partials.tree_node', ['children' => $center->children])
                                </ul>
                            @endif
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</div>


    </div>
</div>
@endcomponent
</section>

@endsection
@section('javascript')
<script>
   
$(document).ready(function () {
    //delete node
    $('.delete-node').on('click', function () {
        debugger;
        var id = $(this).data('id');
        var name = $(this).data('name');
        var deleteUrl = "{{ url('cost-center') }}/" + id;
        swal({
            title: "هل أنت متأكد؟",
            text: "هل تريد حذف " + name + "؟",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        location.reload();
                    }
                });
            }
        });
    });
    $('.cost-center-node').on('click', function () {
        
        // Get data from clicked node
        var id = $(this).data('id');
        var code = $(this).data('code');
        var name = $(this).data('name');
        //var parentId = $(this).data('parent-id');
        debugger;

        var isLast = $(this).data('is-last') ;


        // Fill the form fields
        $('#code').val(code);
        $('#name').val(name);
       
        let parentId = $(this).data('parent-id'); // Ensure this exists on your element

        console.log("Parent ID to select:", parentId); // Debugging step

        if ($('#parent_id option[value="' + parentId + '"]').length > 0) {
            $('#parent_id').val(parentId).trigger('change');
        } else {
            console.log("Error: Parent ID does not exist in dropdown options.");
        }
        $('#is_last_record').prop('disabled', true);
        $('#is_last_record').prop('checked', isLast);
            if(isLast){
                //if true then user can't change the value of is_last_record
                $('#is_last_record').prop('disabled', true);
            }
        // Change the form action to update instead of store
        var updateUrl = "{{ url('cost-center') }}/" + id;
        $('form').attr('action', updateUrl);
        $('form').append('<input type="hidden" name="_method" value="PUT">'); // For Laravel PUT request
    });
});
</script>
<script>
    $.fn.extend({
    treed: function (o) {
      
      var openedClass = 'glyphicon-minus-sign';
      var closedClass = 'glyphicon-plus-sign';
      
      if (typeof o != 'undefined'){
        if (typeof o.openedClass != 'undefined'){
        openedClass = o.openedClass;
        }
        if (typeof o.closedClass != 'undefined'){
        closedClass = o.closedClass;
        }
      };
      
        //initialize each of the top levels
        var tree = $(this);
        tree.addClass("tree");
        //add inidicator class according is_last_record
        tree.find('li').each(function () {
            if($(this).find('span').data('is-last') == 'true'){
                $(this).find('span').addClass('indicator glyphicon ' + closedClass);
            }
        });
        tree.find('li').has("ul").each(function () {
            var branch = $(this); //li with children ul
            branch.prepend("<i class='indicator glyphicon " + closedClass + "'></i>");
            branch.addClass('branch');
            branch.on('click', function (e) {
                if (this == e.target) {
                    var icon = $(this).children('i:first');
                    icon.toggleClass(openedClass + " " + closedClass);
                    $(this).children().children().toggle();
                }
            })
            branch.children().children().toggle();
        });
        //debugger;
        //var isLast=$(this).find('span').data('is-last');
        //if(!isLast){
        //        $(this).find('span').addClass('indicator glyphicon ' + closedClass);
        //    }
        ////fire event from the dynamically added icon
      tree.find('.branch .indicator').each(function(){
        $(this).on('click', function () {
            $(this).closest('li').click();
        });
      });
        //fire event to open branch if the li contains an anchor instead of text
        tree.find('.branch>a').each(function () {
            $(this).on('click', function (e) {
                $(this).closest('li').click();
                e.preventDefault();
            });
        });
        //fire event to open branch if the li contains a button instead of text
        tree.find('.branch>button').each(function () {
            $(this).on('click', function (e) {
                $(this).closest('li').click();
                e.preventDefault();
            });
        });
    }
});

//Initialization of treeviews

$('#tree1').treed();

$('#tree2').treed({openedClass:'glyphicon-folder-open', closedClass:'glyphicon-folder-close'});

$('#tree3').treed({openedClass:'glyphicon-chevron-right', closedClass:'glyphicon-chevron-down'});

</script>

@endsection
