<!-- Lead Product Modal  Start -->
{{ Form::model($lead, array('route' => array('leads.product.save', $lead->id), 'method' => 'POST', 'id'=>'update-lead',  'class'=>'needs-validation', 'novalidate')) }}
        <style>
            .lead-product-suite .section-shell {
                border: 1px solid #e2e8f0;
                border-radius: 22px;
                background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
            }

            .lead-product-suite .table-wrap {
                border: 1px solid #e2e8f0;
                border-radius: 18px;
                overflow: hidden;
                background: #fff;
            }

            .lead-product-suite .section-intro {
                border: 1px solid #e2e8f0;
                border-radius: 18px;
                background: #f8fafc;
                padding: 14px 16px;
                margin-bottom: 16px;
            }

            .lead-product-suite .form-actions {
                border-top: 1px solid #e2e8f0;
                margin-top: 8px;
                padding-top: 18px;
            }
        </style>
        <div class="lead-product-suite">
        <div class="row">
            
            <div class="col-12">
                <div class="card section-shell">
                    <div class="card-body">
                        <div class="section-intro">
                            <strong class="d-block mb-1">Existing lead products</strong>
                            <span class="text-muted">Review the items already attached to this lead before adding or changing product rows.</span>
                        </div>
                        <div class="table-responsive table-wrap">
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
                <div class="card section-shell">
                    <div class="card-body" id="product-rows" data-url="{{ route('leads.product.add-more') }}">
                        <div class="section-intro">
                            <strong class="d-block mb-1">Add more products</strong>
                            <span class="text-muted">Use the rows below to attach additional products and quantities to this lead.</span>
                        </div>

                        @include('leads.add-more-product',['row' => 0])

                    </div>
                </div>
            </div>
            <div class="mt-4 form-actions">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="updateLead">Save
                        Product</button>
                </div>
            </div>
        </div>
        </div>
    {{ Form::close() }}    
<!-- Lead Product Modal  Ends -->
