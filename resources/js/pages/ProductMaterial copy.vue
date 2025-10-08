<div class="col-6">
    <div class="row mt-3 descClassification" v-for="(cardSaveClassifications, indexClassification) in cardSaveClassifications.rows" :key="cardSaveClassifications.newClassificationNo">
        <div class="card mb-2">
                <h5 class="mb-0">
                    <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDescription" aria-expanded="true" aria-controls="collapseDescription">
                        Item No. {{ cardSaveClassifications.newClassificationNo}}

                    </button>
                </h5>
            <div id="collapseDescription" class="collapse show" data-bs-parent="#accordionMain">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="col-12">
                                <button @click="addRowSaveClassification(indexDescription)" type="button" class="btn btn-primary btn-sm" style="float: right !important;"><i class="fas fa-plus"></i> Add Descriptions</button>
                                <br><br>
                            </div>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                    <th scope="col">#</th>
                                    <th scope="col" style="width: 30%;">PartCode/Type</th>
                                    <th scope="col">Description/ItemName</th>
                                    <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>