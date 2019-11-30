import Koa, { Next, DefaultContext, ParameterizedContext } from 'koa';
import KoaRouter, { IRouterParamContext } from 'koa-router';
import bodyParser from 'koa-bodyparser';
import uuid from 'uuid/v4';

class Cat {
    private id: string;
    private name: string;
    private age: number;

    public constructor(id: string, name: string, age: number) {
        this.id = id;
        this.name = name;
        this.age = age;
    }

    public getId(): string {
        return this.id;
    }

    public getName(): string {
        return this.name;
    }

    public getAge(): number {
        return this.age;
    }

    public setName(name: string): void {
        this.name = name;
    }

    public setAge(age: number): void {
        this.age = age;
    }
}

let cats: Cat[] = [
    new Cat('0b8c0ae9-8a4a-4a73-90b7-df68769cd417', 'Garfield', 2),
    new Cat('1f1510e4-b9a4-483e-964a-30f1c2a47b8a', 'Oreo', 3),
    new Cat('ff2e968f-1b3e-48d8-99b0-da04e32fdd72', 'Hunter', 4),
]

class CatsController {
    public static async listCats(): Promise<Cat[]> {
        return cats;
    }

    public static async getCat(context: IRouterParamContext & ParameterizedContext): Promise<Cat|{}> {
        const requestedId = context.params.id || null;

        if (requestedId === null) {
            throw Error('Id is required. Pass it in the body or the url');
        }
        
        for (const cat of cats) {
            if (cat.getId() == requestedId) {
                return cat;
            }
        }

        context.response.status = 404;
        return {};
    }

    public static async createCat(context: ParameterizedContext): Promise<Cat[]> {
        const body: any = context.request.body;
        
        let cat: Cat = new Cat(uuid(), body.name || 'cat', body.age || 2);
        cats.push(cat);

        return cats;
    }

    public static async updateCat(context: ParameterizedContext): Promise<Cat[]> {
        const body: any = context.request.body;
        const requestedId = context.params.id || body.id || null;

        if (requestedId === null) {
            throw Error('Id is required. Pass it in the body or the url');
        }

        for (let cat of cats) {
            if (cat.getId() == context.params.id) {
                cat.setName(body.name || cat.getName());
                cat.setAge(body.age || cat.getAge());
                return cats;
            }
        }

        context.response.status = 404;
        return cats;
    }

    public static async deleteCat(context: IRouterParamContext & ParameterizedContext): Promise<Cat[]> {
        const requestedId = context.params.id || context.request.body.id || null;

        if (requestedId === null) {
            throw Error('Id is required. Pass it in the body or the url');
        }

        for (let index in cats) {
            if (cats[index].getId() == requestedId) {
                cats.splice(parseInt(index), 1);
                return cats;
            }
        }

        context.response.status = 404;
        return cats;
    }
}

const app: Koa = new Koa;
const router: KoaRouter = new KoaRouter;

router.get('/api/cats', CatsController.listCats);
router.get('/api/cats/:id', CatsController.getCat);
router.post('/api/cats', CatsController.createCat);
router.put('/api/cats/:id*', CatsController.updateCat);
router.del('/api/cats/:id*', CatsController.deleteCat);

app.use(bodyParser());
app.use(async (context: DefaultContext, next: Next): Promise<any> => {
    try {
        const results: Cat|Cat[]|{} = await next();
        context.response.body = results;
    } catch (err) {
        console.error(err);
        context.response.status = 400;
        context.response.body = { message: 'Bad Request' };
    }

    context.response.set('Content-Type', 'application/json');    
});
app.use(router.routes());
app.use(router.allowedMethods());

const host: string = '0.0.0.0';
const port: number = 3000;

app.listen(port, host, undefined, () => {
    console.log(`API server started on http://${host}:${port}`);
});