<template>
    <div>
      <div v-for="(item, index) in items" :key="item.itemNo" class="card mb-3">
        <div class="card-header">
          <h5>Item No {{ item.itemNo }}</h5>
        </div>
        <div class="card-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, rowIndex) in item.rows" :key="rowIndex">
                <td>
                  <input
                    type="text"
                    class="form-control"
                    v-model="row.description"
                    placeholder="Enter description"
                  />
                </td>
                <td>
                  <input
                    type="number"
                    class="form-control"
                    v-model="row.quantity"
                    placeholder="Enter quantity"
                  />
                </td>
                <td>
                  <button
                    class="btn btn-danger btn-sm"
                    @click="removeRow(index, rowIndex)"
                  >
                    Remove
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <button class="btn btn-primary btn-sm" @click="addRow(index)">
            Add Row
          </button>
        </div>
      </div>
      <button class="btn btn-success" @click="addCard">Add Item No</button>
    </div>
  </template>

  <script>
  export default {
    data() {
      return {
        items: [
          {
            itemNo: 1,
            rows: [
              { description: "", quantity: 0 },
            ],
          },
        ],
      };
    },
    methods: {
      addCard() {
        const newItemNo = this.items.length + 1;
        this.items.push({
          itemNo: newItemNo,
          rows: [{ description: "", quantity: 0 }],
        });
      },
      addRow(cardIndex) {
        this.items[cardIndex].rows.push({ description: "", quantity: 0 });
      },
      removeRow(cardIndex, rowIndex) {

        this.items[cardIndex].rows.splice(rowIndex, 1);
      },
    },
  };
  </script>

  <style scoped>
  .card {
    border: 1px solid #ddd;
    border-radius: 5px;
  }
  .card-header {
    background-color: #f8f9fa;
    font-weight: bold;
  }
  </style>
