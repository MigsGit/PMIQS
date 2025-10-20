<template>
    <div>
      <!-- Descriptions Table -->
      <div>
        <h4>Descriptions</h4>
        <button @click="addRowSaveDescription" class="btn btn-primary btn-sm">
          Add Description
        </button>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>PartCode/Type</th>
              <th>Description/ItemName</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(description, index) in rowSaveDescriptions" :key="description.descItemNo">
              <td>{{ index + 1 }}</td>
              <td>{{ description.partcodeType }}</td>
              <td>{{ description.descriptionItemName }}</td>
              <td>
                <button @click="removeRowSaveDescription(index)" class="btn btn-danger btn-sm">
                  Remove
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Classification Cards -->
      <div>
        <h4>Classification Cards</h4>
        <div v-for="(card, cardIndex) in cardSaveClassifications" :key="cardIndex" class="card mb-3">
          <div class="card-header">
            <h5>Classification: {{ card.descriptionItemName }}</h5>
          </div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Classification</th>
                  <th>Quantity</th>
                  <th>Unit Price</th>
                  <th>Remarks</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, rowIndex) in card.rows" :key="rowIndex">
                  <td>{{ rowIndex + 1 }}</td>
                  <td>
                    <input
                      type="text"
                      class="form-control"
                      v-model="row.classification"
                      placeholder="Enter classification"
                    />
                  </td>
                  <td>
                    <input
                      type="number"
                      class="form-control"
                      v-model="row.qty"
                      placeholder="Enter quantity"
                    />
                  </td>
                  <td>
                    <input
                      type="text"
                      class="form-control"
                      v-model="row.unitPrice"
                      placeholder="Enter unit price"
                    />
                  </td>
                  <td>
                    <input
                      type="text"
                      class="form-control"
                      v-model="row.remarks"
                      placeholder="Enter remarks"
                    />
                  </td>
                  <td>
                    <button @click="removeRowFromCard(cardIndex, rowIndex)" class="btn btn-danger btn-sm">
                      Remove
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
            <button @click="addRowToCard(cardIndex)" class="btn btn-primary btn-sm">
              Add Row
            </button>
          </div>
        </div>
      </div>
    </div>
    </template>


<script>
import { ref, watch } from 'vue';

export default {
  setup() {
    // Reactive data
    const rowSaveDescriptions = ref([
      {
        descItemNo: 1,
        partcodeType: 'Type A',
        descriptionItemName: 'Item A',
        matSpecsLength: 10,
        matSpecsWidth: 20,
        matSpecsHeight: 30,
        matRawType: 'Raw A',
        matRawThickness: 5,
        matRawWidth: 15,
      },
    ]);

    const cardSaveClassifications = ref([]);

    // Watch rowSaveDescriptions and update cardSaveClassifications
    watch(rowSaveDescriptions, (newDescriptions) => {
      cardSaveClassifications.value = newDescriptions.map((description) => ({
        descriptionItemName: description.descriptionItemName,
        rows: [
          {
            classification: description.partcodeType,
            qty: 0,
            unitPrice: 'pcs',
            remarks: '',
          },
        ],
      }));
    }, { immediate: true });

    // Add a new description
    const addRowSaveDescription = () => {
      const newDescItemNo = rowSaveDescriptions.value.length + 1;
      rowSaveDescriptions.value.push({
        descItemNo: newDescItemNo,
        partcodeType: '',
        descriptionItemName: `Item ${newDescItemNo}`,
        matSpecsLength: 0,
        matSpecsWidth: 0,
        matSpecsHeight: 0,
        matRawType: '',
        matRawThickness: 0,
        matRawWidth: 0,
      });
    };

    // Remove a description
    const removeRowSaveDescription = (index) => {
      rowSaveDescriptions.value.splice(index, 1);
    };

    // Add a new row to a specific card
    const addRowToCard = (cardIndex) => {
      cardSaveClassifications.value[cardIndex].rows.push({
        classification: '',
        qty: 0,
        unitPrice: 'pcs',
        remarks: '',
      });
    };

    // Remove a row from a specific card
    const removeRowFromCard = (cardIndex, rowIndex) => {
      cardSaveClassifications.value[cardIndex].rows.splice(rowIndex, 1);
    };

    return {
      rowSaveDescriptions,
      cardSaveClassifications,
      addRowSaveDescription,
      removeRowSaveDescription,
      addRowToCard,
      removeRowFromCard,
    };
  },
};
</script>