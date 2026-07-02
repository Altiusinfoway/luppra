<!-- Lead Product Modal  Start -->
{{ Form::model($lead, array('route' => array('leads.product.save', $lead->id), 'method' => 'POST', 'id'=>'update-lead',  'class'=>'needs-validation', 'novalidate')) }}
        <div class="row">
            
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                </tr>
                                </thead>
                                
                                <tbody>
                                    @if($lead->product->isNotEmpty())

                                        @foreach($lead->product as $product)
                                        <tr>
                                            <td>
                                                {{ $product->name }}
                                            </td>
                                            <td>
                                                {{ $product->pivot->price }}
                                            </td>
                                            <td>
                                                {{ $product->pivot->qty }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>

                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body" id="product-rows" data-url="{{ route('leads.product.add-more') }}">

                        @include('leads.add-more-product',['row' => 0])

                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="updateLead">Save
                        Product</button>
                </div>
            </div>
        </div>
    {{ Form::close() }}    
<!-- Lead Product Modal  Ends -->
