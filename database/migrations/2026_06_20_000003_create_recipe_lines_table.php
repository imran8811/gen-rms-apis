<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One component line of a recipe. A line is one of:
 *   - ingredient : references an inventory_item (per-unit cost)
 *   - sub_recipe : references another recipe of kind=sub_recipe
 *   - overhead   : a flat per-item cost keyed by `overhead_key` (gas, electricity, labour)
 *
 * Line cost is ALWAYS derived from the component's current unit cost — never
 * stored — so changing an ingredient price re-prices every recipe.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('recipe_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->enum('component_type', ['ingredient', 'sub_recipe', 'overhead']);
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('sub_recipe_id')->nullable()->constrained('recipes')->nullOnDelete();
            $table->string('overhead_key')->nullable();      // gas / electricity / labour ...
            $table->string('label');                          // display name on the line
            $table->decimal('qty', 12, 3)->default(0);
            $table->string('unit', 10)->nullable();           // g / ml / pc — convertible to component base unit
            $table->decimal('flat_cost', 12, 2)->nullable();  // for overhead lines (qty-less)
            $table->decimal('waste_pct', 5, 2)->default(0);   // optional yield loss
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_lines');
    }
};
